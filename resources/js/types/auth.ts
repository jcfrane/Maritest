export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    is_landlord: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Tenant = {
    id: number;
    name: string;
    slug: string;
    domain: string | null;
    logo: string | null;
    settings: Record<string, unknown> | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
