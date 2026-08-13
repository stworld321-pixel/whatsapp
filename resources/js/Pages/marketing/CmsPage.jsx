import { Head } from '@inertiajs/react';
import LandingLayout from '@/Layouts/LandingLayout';

export default function CmsPage({ page }) {
    return (
        <LandingLayout>
            <Head>
                <title>{page.meta_title ?? page.title}</title>
                {page.meta_description && <meta name="description" content={page.meta_description} />}
            </Head>

            <main className="mx-auto max-w-3xl px-4 py-16 sm:py-20">
                <div
                    className="prose dark:prose-invert max-w-none"
                    dangerouslySetInnerHTML={{ __html: page.content ?? '' }}
                />
            </main>
        </LandingLayout>
    );
}

