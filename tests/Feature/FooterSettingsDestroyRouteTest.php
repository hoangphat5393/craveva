<?php

it('resolves footer settings destroy route with numeric id placeholder', function () {
    $template = route('footer-settings.destroy', ':id');

    expect($template)->toContain('footer-settings/:id');
});
