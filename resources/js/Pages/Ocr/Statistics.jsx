import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export default function Statistics({ auth, stats }) {
    const chartData = [
        {
            name: 'Vérifiés',
            value: stats.verified,
            fill: '#4F46E5'
        },
        {
            name: 'Non vérifiés',
            value: stats.total - stats.verified,
            fill: '#E5E7EB'
        },
        {
            name: 'Faible confiance',
            value: stats.low_confidence,
            fill: '#DC2626'
        }
    ];

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Statistiques OCR</h2>}
        >
            <Head title="Statistiques OCR" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {/* Summary Stats */}
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Total des lectures</p>
                                    <p className="text-2xl font-semibold">{stats.total}</p>
                                </div>
                                <div className="bg-gray-50 p-4 rounded">
                                    <p className="text-sm text-gray-600">Lectures vérifiées</p>
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

                            {/* Chart */}
                            <div className="h-96">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={chartData}
                                        margin={{
                                            top: 20,
                                            right: 30,
                                            left: 20,
                                            bottom: 5,
                                        }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="name" />
                                        <YAxis />
                                        <Tooltip />
                                        <Legend />
                                        <Bar dataKey="value" name="Nombre de lectures" />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>

                            {/* Performance Metrics */}
                            <div className="mt-8">
                                <h3 className="text-lg font-medium mb-4">Métriques de performance</h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="bg-gray-50 p-4 rounded">
                                        <p className="text-sm text-gray-600">Taux de vérification</p>
                                        <p className="text-2xl font-semibold">
                                            {stats.total > 0
                                                ? Math.round((stats.verified / stats.total) * 100)
                                                : 0}%
                                        </p>
                                    </div>
                                    <div className="bg-gray-50 p-4 rounded">
                                        <p className="text-sm text-gray-600">Taux d'erreur</p>
                                        <p className="text-2xl font-semibold">
                                            {stats.total > 0
                                                ? Math.round((stats.low_confidence / stats.total) * 100)
                                                : 0}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}