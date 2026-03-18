import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, readonly, ref } from 'vue';
import { store as tenantImagesStore } from '@/routes/tenant/images';

const pendingUploads = ref(0);

type UploadImageResponse = {
    url: string;
};

export function useTenantImageUpload() {
    const page = usePage();
    const tenantSlug = computed(
        () => (page.props.currentTenant as { slug?: string } | null)?.slug ?? '',
    );

    async function uploadImage(file: File): Promise<string> {
        if (!tenantSlug.value) {
            throw new Error('Tenant context is required to upload images.');
        }

        pendingUploads.value += 1;

        try {
            const formData = new FormData();
            formData.append('image', file);

            const response = await axios.post<UploadImageResponse>(
                tenantImagesStore.url(tenantSlug.value),
                formData,
                {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                },
            );

            return response.data.url;
        } finally {
            pendingUploads.value -= 1;
        }
    }

    return {
        pendingUploads: readonly(pendingUploads),
        tenantSlug,
        uploadImage,
    };
}
