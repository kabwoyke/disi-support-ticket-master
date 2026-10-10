<?php

namespace App\Support;

/**
 * In-memory relevance search for DISI-Solves.
 *
 * Built for a modest internal corpus (a few thousand issues), so it scores in PHP instead of
 * depending on an external search service. It handles:
 *  - stop-word removal and light stemming (scanning / scanned / scanners -> scan)
 *  - prefix matches and typo tolerance (levenshtein) against the corpus vocabulary
 *  - a small domain synonym table (jam ~ stuck ~ misfeed, blurry ~ fuzzy, ...)
 *  - field weighting: title > category > accepted answers > description
 *  - a coverage penalty so documents matching more of the query words win
 *  - "did you mean" suggestions built from words actually used in the corpus
 */
class IssueSearch
{
    private const STOPWORDS = [
        'a', 'an', 'the', 'and', 'or', 'but', 'of', 'to', 'in', 'on', 'at', 'for', 'with', 'from', 'by', 'is', 'are',
        'was', 'were', 'be', 'been', 'it', 'its', 'this', 'that', 'these', 'those', 'i', 'we', 'you', 'my', 'our',
        'me', 'can', 'could', 'should', 'would', 'do', 'does', 'did', 'how', 'why', 'what', 'when', 'where', 'which',
        'not', 'no', 'cant', 'cannot', 'wont', 'dont', 'doesnt', 'has', 'have', 'had', 'get', 'gets', 'getting',
        'got', 'if', 'so', 'as', 'any', 'some', 'there', 'then', 'than', 'about', 'into', 'out', 'up', 'will',
    ];

    /** Words in the same group are treated as near-equivalent. */
    private const SYNONYMS = [
        ['scanner', 'scan', 'scanning', 'scanned'],
        ['jam', 'jammed', 'stuck', 'misfeed', 'misfeeds', 'clog', 'clogged', 'blocked'],
        ['error', 'fail', 'failure', 'failed', 'fault', 'problem', 'issue', 'bug', 'broken'],
        ['crash', 'freeze', 'frozen', 'hang', 'hung', 'unresponsive', 'stall', 'stalled'],
        ['slow', 'lag', 'laggy', 'sluggish', 'delay', 'delayed'],
        ['install', 'setup', 'installation', 'configure', 'configuration', 'config'],
        ['driver', 'firmware', 'software'],
        ['calibrate', 'calibration', 'align', 'alignment', 'adjust'],
        ['blur', 'blurry', 'fuzzy', 'unclear', 'unreadable', 'faded'],
        ['line', 'lines', 'streak', 'streaks', 'stripe', 'stripes', 'smear'],
        ['network', 'connection', 'connect', 'offline', 'disconnect', 'disconnected'],
        ['license', 'licence', 'activation', 'activate', 'expired'],
        ['update', 'upgrade', 'patch'],
        ['clean', 'cleaning', 'dirty', 'dust', 'roller', 'rollers'],
        ['boot', 'start', 'startup', 'power', 'turn'],
        ['double', 'multifeed', 'overlap'],
    ];

    /** field => weight */
    private const WEIGHTS = ['title' => 5.0, 'category' => 4.0, 'answers' => 3.0, 'description' => 2.0];

    private array $synonymIndex = [];

    public function __construct()
    {
        foreach (self::SYNONYMS as $group) {
            $stems = array_unique(array_map(fn ($w) => $this->stem($w), $group));
            foreach ($stems as $s) {
                $this->synonymIndex[$s] = array_values(array_diff($stems, [$s]));
            }
        }
    }

    /**
     * @param  array<int, array{id:int,title:string,description:string,category:string,answers:string,views?:int}>  $docs
     * @return array{results: array<int, array>, suggestion: ?string, terms: array<int,string>}
     */
    public function search(string $query, array $docs, int $limit = 30): array
    {
        $tokens = $this->queryTokens($query);

        if ($tokens === []) {
            return ['results' => [], 'suggestion' => null, 'terms' => []];
        }

        // Index every document once: field => [stem => count], plus stem => surface words.
        $index = [];
        $vocab = [];      // stem => true
        $surfaces = [];   // stem => [surface => count]
        foreach ($docs as $i => $doc) {
            foreach (['title', 'description', 'answers', 'category'] as $field) {
                $terms = [];
                foreach ($this->tokenize($doc[$field] ?? '') as $surface) {
                    $stem = $this->stem($surface);
                    $terms[$stem] = ($terms[$stem] ?? 0) + 1;
                    $vocab[$stem] = true;
                    $surfaces[$stem][$surface] = ($surfaces[$stem][$surface] ?? 0) + 1;
                }
                $index[$i][$field] = $terms;
            }
        }

        // Expand each query token into corpus terms it could mean, with a confidence weight.
        $vocabTerms = array_keys($vocab);
        $expansions = [];
        foreach ($tokens as $token) {
            $expansions[$token] = $this->expand($token, $vocabTerms);
        }

        $scored = [];

        foreach ($docs as $i => $doc) {
            $matchedTokens = 0;
            $fieldScore = array_fill_keys(array_keys(self::WEIGHTS), 0.0);
            $hits = [];

            foreach ($tokens as $token) {
                $tokenHit = false;
                foreach (self::WEIGHTS as $field => $weight) {
                    $best = 0.0;
                    foreach ($expansions[$token] as $term => $confidence) {
                        $count = $index[$i][$field][$term] ?? 0;
                        if ($count > 0) {
                            $best = max($best, $confidence * (1 + 0.3 * log($count)));
                            $hits[$term] = true;
                        }
                    }
                    if ($best > 0) {
                        $tokenHit = true;
                        $fieldScore[$field] += $best * $weight;
                    }
                }
                $matchedTokens += $tokenHit ? 1 : 0;
            }

            if ($matchedTokens === 0) {
                continue;
            }

            $coverage = $matchedTokens / count($tokens);
            $total = array_sum($fieldScore) * (0.35 + 0.65 * $coverage);

            // Exact phrase bonuses.
            if (count($tokens) > 1) {
                if (stripos($doc['title'], $query) !== false) {
                    $total += 10;
                } elseif (stripos($doc['description'], $query) !== false) {
                    $total += 5;
                }
            }

            // Solved issues are more useful; popular ones slightly more so.
            if (($doc['answers'] ?? '') !== '') {
                $total *= 1.15;
            }
            $total *= 1 + min(0.15, log(1 + ($doc['views'] ?? 0)) / 60);

            $scored[] = [
                'index' => $i,
                'score' => $total,
                'matched_in' => array_search(max($fieldScore), $fieldScore),
                'coverage' => $coverage,
                'highlight' => $this->surfaceWords(array_keys($hits), $surfaces),
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $results = array_map(function ($row) use ($docs) {
            $doc = $docs[$row['index']];
            $source = $row['matched_in'] === 'answers' && ($doc['answers'] ?? '') !== ''
                ? $doc['answers']
                : $doc['description'];

            return [
                'id' => $doc['id'],
                'score' => round($row['score'], 2),
                'matched_in' => $row['matched_in'],
                'highlight' => $row['highlight'],
                'snippet' => $this->snippet($source, $row['highlight']),
                'exact' => $row['coverage'] >= 1.0,
            ];
        }, array_slice($scored, 0, $limit));

        return [
            'results' => $results,
            'total' => count($scored),
            'suggestion' => $this->suggest($query, $vocab, $surfaces),
            'terms' => $this->tokenize($query),
        ];
    }

    /** Lower-cased words without punctuation. */
    public function tokenize(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $m);

        return $m[0];
    }

    /** Stemmed query tokens with stop-words removed (kept if the query is *only* stop-words). */
    private function queryTokens(string $query): array
    {
        $words = $this->tokenize($query);
        $kept = array_filter($words, fn ($w) => ! in_array($w, self::STOPWORDS, true));
        $words = $kept !== [] ? $kept : $words;

        return array_values(array_unique(array_map(fn ($w) => $this->stem($w), $words)));
    }

    /** Very small suffix stripper; good enough for consistent matching, not linguistics. */
    private function stem(string $w): string
    {
        $len = strlen($w);
        if ($len <= 3) {
            return $w;
        }

        if (str_ends_with($w, 'ies') && $len > 4) {
            return substr($w, 0, -3) . 'y';
        }
        if (str_ends_with($w, 'sses')) {
            return substr($w, 0, -2);
        }
        foreach (['ing' => 5, 'ed' => 4] as $suffix => $minLen) {
            if (str_ends_with($w, $suffix) && $len > $minLen) {
                $base = substr($w, 0, -strlen($suffix));

                // scanning -> scann -> scan
                return preg_match('/([b-df-hj-np-tv-z])\1$/', $base) ? substr($base, 0, -1) : $base;
            }
        }
        if (preg_match('/(s|x|z|ch|sh)es$/', $w)) {
            return substr($w, 0, -2);
        }
        if (str_ends_with($w, 's') && ! str_ends_with($w, 'ss') && ! str_ends_with($w, 'us')) {
            return substr($w, 0, -1);
        }

        return $w;
    }

    /**
     * @param  array<int,string>  $vocab
     * @return array<string,float> corpus term => confidence (0..1)
     */
    private function expand(string $token, array $vocab): array
    {
        $out = [];
        $len = strlen($token);

        foreach ($vocab as $term) {
            if ($term === $token) {
                $out[$term] = 1.0;
                continue;
            }

            $tl = strlen($term);
            // prefix either way ("instal" ~ "install"), only for reasonably long words
            if ($len >= 4 && $tl >= 4 && (str_starts_with($term, $token) || str_starts_with($token, $term))) {
                $out[$term] = max($out[$term] ?? 0, 0.8);
                continue;
            }

            // typo tolerance, stricter for short words
            if ($len >= 4 && abs($len - $tl) <= 2) {
                $distance = levenshtein($token, $term);
                if ($distance <= ($len <= 5 ? 1 : 2)) {
                    $out[$term] = max($out[$term] ?? 0, 0.7 - 0.15 * ($distance - 1));
                }
            }
        }

        foreach ($this->synonymIndex[$token] ?? [] as $syn) {
            $out[$syn] = max($out[$syn] ?? 0, 0.6);
        }

        return $out;
    }

    /** Readable surface forms of matched stems, used for highlighting. */
    private function surfaceWords(array $stems, array $surfaces): array
    {
        $words = [];
        foreach ($stems as $stem) {
            foreach (array_keys($surfaces[$stem] ?? []) as $surface) {
                if (mb_strlen($surface) >= 3) {
                    $words[$surface] = true;
                }
            }
        }

        return array_slice(array_keys($words), 0, 15);
    }

    private function snippet(string $text, array $highlight, int $radius = 90): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        if (mb_strlen($text) <= $radius * 2) {
            return $text;
        }

        $pos = 0;
        if ($highlight !== []) {
            $pattern = '/' . implode('|', array_map(fn ($w) => preg_quote($w, '/'), $highlight)) . '/iu';
            if (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE)) {
                $pos = mb_strlen(substr($text, 0, $m[0][1]));
            }
        }

        $start = max(0, $pos - $radius);
        $ellipsis = "\u{2026}";

        return ($start > 0 ? $ellipsis : '')
            . mb_substr($text, $start, $radius * 2)
            . ($start + $radius * 2 < mb_strlen($text) ? $ellipsis : '');
    }

    /** "Did you mean": replace query words absent from the corpus with the closest word that is present. */
    private function suggest(string $query, array $vocab, array $surfaces): ?string
    {
        $changed = false;
        $out = [];

        foreach ($this->tokenize($query) as $word) {
            if (in_array($word, self::STOPWORDS, true) || strlen($word) < 4 || isset($vocab[$this->stem($word)])) {
                $out[] = $word;
                continue;
            }

            $best = null;
            $bestDistance = strlen($word) <= 5 ? 2 : 3;
            foreach ($surfaces as $forms) {
                foreach (array_keys($forms) as $surface) {
                    if (abs(strlen($surface) - strlen($word)) > 2) {
                        continue;
                    }
                    $d = levenshtein($word, $surface);
                    if ($d > 0 && $d < $bestDistance) {
                        $bestDistance = $d;
                        $best = $surface;
                    }
                }
            }

            $out[] = $best ?? $word;
            $changed = $changed || $best !== null;
        }

        return $changed ? implode(' ', $out) : null;
    }
}
