//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

import { createInertiaApp } from '@inertiajs/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
const queryClient = new QueryClient()


createInertiaApp({
      pages: {
        path: './Pages',
        extension: '.tsx',
        lazy: true,
        transform: (name, page) => name.replace('/', '-'),
    },

      resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.tsx')
        return pages[`./Pages/${name}.tsx`]()
    },
})



