import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

function formatBytes(bytes) {
    const value = Number(bytes) || 0;
    if (value < 1024) return `${value} B`;
    if (value < 1024 * 1024) return `${(value / 1024).toFixed(1)} KB`;
    if (value < 1024 * 1024 * 1024) return `${(value / (1024 * 1024)).toFixed(1)} MB`;
    return `${(value / (1024 * 1024 * 1024)).toFixed(2)} GB`;
}

const LABELS = {
    storage: 'Storage',
    users: 'Users',
    inbox_agents: 'Inbox agents',
    whatsapp_accounts: 'WhatsApp accounts',
    whatsapp_templates: 'WhatsApp templates',
    whatsapp_messages_per_month: 'WhatsApp messages',
    campaigns_per_month: 'Campaigns',
    sms_per_month: 'SMS messages',
    emails_per_month: 'Emails',
    ai_tokens_per_month: 'AI tokens',
    knowledge_bases: 'Knowledge bases',
    chatbots: 'Chatbots',
    social_accounts: 'Social accounts',
    social_posts_per_month: 'Social posts',
    lead_credits_per_month: 'Lead credits',
    automations: 'Automations',
};

function formatUsageValue(key, value) {
    if (key === 'storage') {
        return formatBytes(value);
    }

    return Number.isFinite(Number(value)) ? String(Number(value)) : String(value ?? 0);
}

export default function UsageBanner({ usage }) {
    const { t } = useTranslation();
    const [dismissed, setDismissed] = useState(false);

    const rows = Object.entries(usage ?? {})
        .filter(([, entry]) => entry && typeof entry === 'object' && entry.limit !== null && entry.limit !== undefined)
        .filter(([, entry]) => Number(entry.percent ?? 0) >= 80);

    if (dismissed || rows.length === 0) return null;

    const [key, data] = rows.sort((a, b) => Number(b[1].percent ?? 0) - Number(a[1].percent ?? 0))[0];
    const label = LABELS[key] ?? key.replace(/_per_month$/, '').replace(/_/g, ' ');
    const currentLabel = formatUsageValue(key, data.current);
    const limitLabel = formatUsageValue(key, data.limit);

    return (
        <div className="flex items-center justify-between gap-4 border-b border-amber-200 bg-amber-50 px-4 py-2 text-sm dark:border-amber-800 dark:bg-amber-900/20">
            <div className="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                <svg className="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                <span>
                    {t('ui.usage_banner', { percent: data.percent, label, current: currentLabel, limit: limitLabel })}{' '}
                    <Link href={route('client.pricing')} className="font-semibold underline hover:no-underline">
                        {t('ui.usage_banner_upgrade')}
                    </Link>{' '}
                    {t('ui.usage_banner_suffix')}
                </span>
            </div>
            <button
                onClick={() => setDismissed(true)}
                className="flex-shrink-0 text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200"
                aria-label={t('common.dismiss')}
            >
                <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>
            </button>
        </div>
    );
}
