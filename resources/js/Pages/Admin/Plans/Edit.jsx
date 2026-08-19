import { useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button, Modal } from '@/Components/ui';
import PlanForm from './PlanForm';
import { useTranslation } from 'react-i18next';

const emptyPlan = (currency = 'INR') => ({
    name: '',
    slug: '',
    description: '',
    currency_code: currency,
    monthly_price_cents: null,
    yearly_price_cents: null,
    trial_days: 0,
    stripe_monthly_id: '',
    stripe_yearly_id: '',
    features: [],
    limits: {},
    enabled: true,
    featured: false,
    popular: false,
    sort_order: 0,
    white_label_enabled: false,
});

export default function AdminPlansEdit({ plan, currencies = [], defaultCurrency = 'INR' }) {
    const { t } = useTranslation();
    const isEdit = !!plan?.id;

    const { data, setData, post, put, processing, errors, reset } = useForm(
        plan ? { ...plan, limits: plan.limits ?? {}, features: plan.features ?? [] } : emptyPlan(defaultCurrency)
    );

    const goBack = () => window.history.back();

    const handleSubmit = (e) => {
        e.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                reset();
            },
        };

        if (isEdit) {
            put(route('admin.plans.update', plan.id), options);
            return;
        }

        post(route('admin.plans.store'), options);
    };

    return (
        <AdminLayout title={isEdit ? t('admin.edit_plan_with_name', { name: plan.name }) : t('admin.new_plan')}>
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                            {isEdit ? t('admin.edit_plan') : t('admin.add_plan')}
                        </h1>
                        <p className="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">
                            {t('admin.plan_limits')}
                        </p>
                    </div>
                    <Button type="button" variant="outline" onClick={goBack}>
                        {t('admin.back_to_plans')}
                    </Button>
                </div>

                <div className="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-sm">
                    <PlanForm
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onSubmit={handleSubmit}
                        onCancel={goBack}
                        isEdit={isEdit}
                        currencies={currencies}
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
