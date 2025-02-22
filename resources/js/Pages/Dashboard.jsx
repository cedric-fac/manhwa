import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { 
    CalendarIcon, 
    CurrencyIcon, 
    ClockIcon, 
    InboxIcon,
    AlertTriangleIcon,
    BarChart3Icon,
    ActivityIcon,
    BellIcon
} from 'lucide-react';

export default function Dashboard({ auth, stats }) {
    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('fr-FR', {
            style: 'currency',
            currency: 'XAF'
        }).format(amount);
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('fr-FR');
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Tableau de bord
                </h2>
            }
        >
            <Head title="Tableau de bord" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Overview Stats */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center gap-4">
                                <div className="p-3 bg-blue-100 rounded-full">
                                    <InboxIcon className="w-6 h-6 text-blue-600" />
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Total Factures</p>
                                    <p className="text-2xl font-semibold">{stats.overview.total_invoices}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center gap-4">
                                <div className="p-3 bg-yellow-100 rounded-full">
                                    <ClockIcon className="w-6 h-6 text-yellow-600" />
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Non Payées</p>
                                    <p className="text-2xl font-semibold">{stats.overview.total_unpaid}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center gap-4">
                                <div className="p-3 bg-red-100 rounded-full">
                                    <AlertTriangleIcon className="w-6 h-6 text-red-600" />
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">En Retard</p>
                                    <p className="text-2xl font-semibold">{stats.overview.total_overdue}</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center gap-4">
                                <div className="p-3 bg-green-100 rounded-full">
                                    <CurrencyIcon className="w-6 h-6 text-green-600" />
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Montant Dû</p>
                                    <p className="text-2xl font-semibold">
                                        {formatCurrency(stats.overview.total_amount_due)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Reminder Stats */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-medium mb-4">Statistiques des Rappels</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Total Envoyés</p>
                                    <p className="text-xl font-semibold">{stats.reminders.total_sent}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Échecs</p>
                                    <p className="text-xl font-semibold">{stats.reminders.total_failed}</p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Taux de Succès</p>
                                    <p className="text-xl font-semibold">{stats.reminders.success_rate}%</p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-medium mb-4">Performance</h3>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <p className="text-sm text-gray-600">Délai Moyen de Paiement</p>
                                    <p className="text-xl font-semibold">
                                        {stats.performance.avg_days_to_payment} jours
                                    </p>
                                </div>
                                <div>
                                    <p className="text-sm text-gray-600">Taux de Paiement après Rappel</p>
                                    <p className="text-xl font-semibold">
                                        {stats.performance.payment_rate_after_reminder}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Activity */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-medium mb-4">Derniers Rappels</h3>
                            <div className="space-y-4">
                                {stats.recent.recent_reminders.map((reminder) => (
                                    <div key={reminder.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">{reminder.invoice.client.name}</p>
                                            <p className="text-sm text-gray-600">
                                                Facture {reminder.invoice.number}
                                            </p>
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            {formatDate(reminder.sent_at)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 className="text-lg font-medium mb-4">Derniers Paiements</h3>
                            <div className="space-y-4">
                                {stats.recent.recent_payments.map((payment) => (
                                    <div key={payment.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">{payment.client.name}</p>
                                            <p className="text-sm text-gray-600">
                                                {formatCurrency(payment.amount_ttc)}
                                            </p>
                                        </div>
                                        <div className="text-sm text-gray-600">
                                            {formatDate(payment.paid_at)}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
