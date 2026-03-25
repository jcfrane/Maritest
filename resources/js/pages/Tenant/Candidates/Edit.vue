<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PagePanel from '@/components/page/PagePanel.vue';
import PageShell from '@/components/page/PageShell.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import TenantLayout from '@/layouts/TenantLayout.vue';
import { dashboard } from '@/routes/tenant';
import {
    edit as candidatesEdit,
    index as candidatesIndex,
    update as candidatesUpdate,
} from '@/routes/tenant/candidates';
import type { BreadcrumbItem, User } from '@/types';

type Props = {
    candidate: {
        data: User;
    };
};

const props = defineProps<Props>();

const page = usePage();
const tenant = page.props.currentTenant;
const slug = tenant?.slug ?? '';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard.url(slug) },
    { title: 'Candidates', href: candidatesIndex.url(slug) },
    {
        title: 'Edit Candidate',
        href: candidatesEdit.url({
            tenant: slug,
            candidate: props.candidate.data.id,
        }),
    },
];

const form = useForm({
    name: props.candidate.data.name,
    email: props.candidate.data.email,
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.put(
        candidatesUpdate.url({
            tenant: slug,
            candidate: props.candidate.data.id,
        }),
    );
}
</script>

<template>
    <Head :title="`${tenant?.name} - Edit Candidate`" />

    <TenantLayout :breadcrumbs="breadcrumbs">
        <PageShell
            title="Edit Candidate"
            description="Update candidate account details without changing it into an internal user."
            content-class="gap-6"
        >
            <PagePanel
                panel-class="mx-auto w-full max-w-3xl"
                body-class="p-6 md:p-8"
            >
                <template #header>
                    <div class="space-y-1">
                        <h2
                            class="text-lg font-semibold tracking-tight text-foreground"
                        >
                            Account details
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Update the core account information while keeping
                            this person assigned as a candidate.
                        </p>
                    </div>
                </template>

                <form
                    class="mx-auto w-full max-w-xl space-y-6"
                    @submit.prevent="submit"
                >
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Leave blank to keep current"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">
                            Confirm Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                        />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            Update Candidate
                        </Button>
                        <Link :href="candidatesIndex.url(slug)">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </PagePanel>
        </PageShell>
    </TenantLayout>
</template>
