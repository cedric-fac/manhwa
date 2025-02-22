import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth, stats, pendingReviews }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Apprentissage OCR</h2>}
        >
            <Head title="Apprentissage OCR" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Stats Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Statistiques OCR</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Total</p>
                                    <p className="text-2xl font-semibold">{stats.total}</p>
                                </div>
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Vérifiés</p>
                                    <p className="text-2xl font-semibold">{stats.verified}</p>
                                </div>
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Faible confiance</p>
                                    <p className="text-2xl font-semibold">{stats.low_confidence}</p>
                                </div>
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Confiance moyenne</p>
                                    <p className="text-2xl font-semibold">{Math.round(stats.avg_confidence)}%</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Pending Reviews Section */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <h3 className="text-lg font-medium mb-4">Révisions en attente</h3>
                            {pendingReviews.data.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Client
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Texte original
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Confiance
                                                </th>
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {pendingReviews.data.map((review) => (
                                                <tr key={review.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm font-medium text-gray-900">
                                                            {review.reading.client.name}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm text-gray-900">
                                                            {review.original_text}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="text-sm text-gray-900">
                                                            {Math.round(review.confidence)}%
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <a
                                                            href={route('ocr.review', review.id)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Réviser
                                                        </a>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <p className="text-gray-500">Aucune révision en attente.</p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}