import { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { 
    FileTextIcon, 
    DownloadIcon, 
    SendIcon,
    EyeIcon,
    FilterIcon 
} from 'lucide-react';

export default function Index({ auth, invoices, filters }) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [selectedClient, setSelectedClient] = useState(filters.client_id || '');

    const statuses = {
        draft: { label: 'Brouillon', color: 'gray' },
        sent: { label: 'Envoyée', color: 'blue' },
        paid: { label: 'Payée', color: 'green' },
        overdue: { label: 'En retard', color: 'red' }
    };

    const handleFilterChange = (newFilters) => {
        router.get(route('invoices.index'), {
            ...filters,
            ...newFilters
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Gestion des Factures
                    </h2>
                    <div className="flex items-center gap-4">
                        {/* Status Filter */}
                        <select
                            value={selectedStatus}
                            onChange={(e) => {
                                setSelectedStatus(e.target.value);
                                handleFilterChange({ status: e.target.value });
                            }}
                            className="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Tous les statuts</option>
                            {Object.entries(statuses).map(([value, { label }]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </select>

                        {/* Client Filter */}
                        <select
                            value={selectedClient}
                            onChange={(e) => {
                                setSelectedClient(e.target.value);
                                handleFilterChange({ client_id: e.target.value });
                            }}
                            className="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="">Tous les clients</option>
                            {/* TODO: Add client options */}
                        </select>
                    </div>
                </div>
            }
        >
            <Head title="Factures" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Numéro
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Montant TTC
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date d'échéance
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Statut
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {invoices.data.map((invoice) => (
                                        <tr key={invoice.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {invoice.number}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {invoice.client.name}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {new Intl.NumberFormat('fr-FR', {
                                                        style: 'currency',
                                                        currency: 'XAF'
                                                    }).format(invoice.amount_ttc)}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {new Date(invoice.due_date).toLocaleDateString()}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statuses[invoice.status].color}-100 text-${statuses[invoice.status].color}-800`}>
                                                    {statuses[invoice.status].label}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div className="flex justify-end gap-2">
                                                    <button
                                                        onClick={() => router.get(route('invoices.show', invoice.id))}
                                                        className="text-blue-600 hover:text-blue-900"
                                                        title="Voir"
                                                    >
                                                        <EyeIcon className="w-5 h-5" />
                                                    </button>
                                                    <button
                                                        onClick={() => router.get(route('invoices.download', invoice.id))}
                                                        className="text-green-600 hover:text-green-900"
                                                        title="Télécharger"
                                                    >
                                                        <DownloadIcon className="w-5 h-5" />
                                                    </button>
                                                    {invoice.status === 'draft' && (
                                                        <button
                                                            onClick={() => router.post(route('invoices.send', invoice.id))}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                            title="Envoyer"
                                                        >
                                                            <SendIcon className="w-5 h-5" />
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            {/* Pagination */}
                            <div className="mt-4">
                                {invoices.links && (
                                    <div className="flex justify-between items-center">
                                        <div className="text-sm text-gray-700">
                                            Affichage de {invoices.from} à {invoices.to} sur {invoices.total} résultats
                                        </div>
                                        <div className="flex gap-1">
                                            {invoices.links.map((link, i) => (
                                                <button
                                                    key={i}
                                                    onClick={() => link.url && router.get(link.url)}
                                                    className={`px-4 py-2 text-sm rounded ${
                                                        link.active
                                                            ? 'bg-blue-600 text-white'
                                                            : 'text-gray-700 hover:bg-gray-100'
                                                    } ${!link.url && 'opacity-50 cursor-not-allowed'}`}
                                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                                />
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}