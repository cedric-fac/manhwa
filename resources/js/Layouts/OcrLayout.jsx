import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { BarChart3, FileText, Settings } from 'lucide-react';

export default function OcrLayout({ user, title, children }) {
    const navigation = [
        {
            name: 'Tableau de bord',
            href: route('ocr.dashboard'),
            icon: BarChart3,
            current: route().current('ocr.dashboard')
        },
        {
            name: 'Révisions',
            href: route('ocr.reviews'),
            icon: FileText,
            current: route().current('ocr.reviews')
        },
        {
            name: 'Statistiques',
            href: route('ocr.statistics'),
            icon: Settings,
            current: route().current('ocr.statistics')
        }
    ];

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ');
    }

    return (
        <AuthenticatedLayout user={user}>
            <Head title={title} />

            <div className="min-h-screen bg-gray-100">
                {/* Navigation */}
                <div className="border-b border-gray-200 bg-white">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex h-16 justify-between">
                            <div className="flex">
                                <div className="flex space-x-8">
                                    {navigation.map((item) => (
                                        <Link
                                            key={item.name}
                                            href={item.href}
                                            className={classNames(
                                                item.current
                                                    ? 'border-indigo-500 text-gray-900'
                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                                                'inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium'
                                            )}
                                        >
                                            <item.icon className="mr-2 h-5 w-5" />
                                            {item.name}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Page Content */}
                <main className="py-10">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div className="px-4 py-6 sm:px-0">
                            <div className="mb-6">
                                <h1 className="text-2xl font-semibold text-gray-900">{title}</h1>
                            </div>
                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </AuthenticatedLayout>
    );
}