<?php

use Livewire\Component;

new class extends Component
{
    //
    public function render(){
        return view("pages.ict.⚡dashboard")->layout('layouts::support');
    }

};
?>

<div>

    <livewire:dashboard.index/>
</div>
