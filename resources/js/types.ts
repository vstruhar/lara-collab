export type User = {
    id: BigInteger;
    name: String;
    email: String;
    phone: String;
    job_title: String;
    avatar: String;
    rate: BigInteger;
    created_at: String;
};

export type PricingType = 'hourly' | 'fixed';
