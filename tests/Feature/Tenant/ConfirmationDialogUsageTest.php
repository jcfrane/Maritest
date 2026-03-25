<?php

dataset('tenant confirmation pages', [
    'users index' => 'resources/js/pages/Tenant/Users/Index.vue',
    'candidates index' => 'resources/js/pages/Tenant/Candidates/Index.vue',
    'exam sets index' => 'resources/js/pages/Tenant/ExamSets/Index.vue',
    'questionnaires index' => 'resources/js/pages/Tenant/Questionnaires/Index.vue',
]);

test('tenant management pages use a shared confirmation dialog instead of native confirm', function (string $path) {
    $contents = file_get_contents(base_path($path));

    expect($contents)
        ->not->toBeFalse()
        ->toContain("import ConfirmDialog from '@/components/ConfirmDialog.vue';")
        ->toContain('<ConfirmDialog')
        ->not->toContain('confirm(');
})->with('tenant confirmation pages');

test('shared confirm dialog is built with the shadcn dialog primitives', function () {
    $contents = file_get_contents(resource_path('js/components/ConfirmDialog.vue'));

    expect($contents)
        ->not->toBeFalse()
        ->toContain("from '@/components/ui/dialog';")
        ->toContain('<Dialog')
        ->toContain('<DialogContent');
});
