import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    ArrowLeftIcon,
    DownloadIcon,
    SendIcon,
    CheckCircleIcon,
    BanknoteIcon 
} from 'lucide-react';

export default function Show({ auth, invoice }) {
    const statuses = {
        draft: { label: 'Brouillon', color: 'gray' },
        sent: { label: 'Envoyée', color: 'blue' },
        paid: { label: 'Payée', color: 'green' },
        overdue: { label: 'En retard', color: 'red' }
    };

    const handleStatusUpdate = (newStatus) => {
        if (confirm('Êtes-vous sûr de vouloir changer le statut de cette facture ?')) {
            router.patch(route('invoices.status.update', invoice.id), {
                status: newStatus
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center">
                    <button
                        onClick={() => window.history.back()}
                        className="mr-4 text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeftIcon className="w-5 h-5" />
                    </button>
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Facture {invoice.number}
                    </h2>
                </div>
            }
        >
            <Head title={`Facture ${invoice.number}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Actions */}
                    <div className="mb-6 flex justify-end gap-4">
                        <button
                            onClick={() => router.get(route('invoices.download', invoice.id))}
                            className="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            <DownloadIcon className="w-5 h-5 mr-2" />
                            Télécharger
                        </button>
                        {invoice.status === 'draft' && (
                            <button
                                onClick={() => router.post(route('invoices.send', invoice.id))}
                                className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                <SendIcon className="w-5 h-5 mr-2" />
                                Envoyer
                            </button>
                        )}
                        {invoice.status !== 'paid' && (
                            <button
                                onClick={() => handleStatusUpdate('paid')}
                                className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                <CheckCircleIcon className="w-5 h-5 mr-2" />
                                Marquer comme payée
                            </button>
                        )}
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 space-y-6">
                            {/* Status Banner */}
                            <div className={`p-4 rounded-lg bg-${statuses[invoice.status].color}-100`}>
                                <div className="flex">
                                    <div className="flex-shrink-0">
                                        <BanknoteIcon className={`h-5 w-5 text-${statuses[invoice.status].color}-400`} />
                                    </div>
                                    <div className="ml-3">
                                        <h3 className={`text-sm font-medium text-${statuses[invoice.status].color}-800`}>
                                            Statut: {statuses[invoice.status].label}
                                        </h3>
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Invoice Details */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Détails de la facture
                                    </h3>
                                    <dl className="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Numéro</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{invoice.number}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Date d'émission</dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {new Date(invoice.created_at).toLocaleDateString()}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Date d'échéance</dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {new Date(invoice.due_date).toLocaleDateString()}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                {/* Client Information */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Informations client
                                    </h3>
                                    <dl className="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Nom</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{invoice.client.name}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Adresse</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{invoice.client.address}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Téléphone</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{invoice.client.phone}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            {/* Reading Details */}
                            <div className="mt-8">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Détails du relevé
                                </h3>
                                <dl className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Valeur relevée</dt>
                                        <dd className="mt-1 text-sm text-gray-900">{invoice.reading.value}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500">Date du relevé</dt>
                                        <dd className="mt-1 text-sm text-gray-900">
                                            {new Date(invoice.reading.read_at).toLocaleDateString()}
                                        </dd>
                                    </div>
                                    {invoice.reading.photo_url && (
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Photo</dt>
                                            <dd className="mt-1">
                                                <a
                                                    href={invoice.reading.photo_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="text-blue-600 hover:text-blue-900"
                                                >
                                                    Voir la photo
                                                </a>
                                            </dd>
                                        </div>
                                    )}
                                </dl>
                            </div>

                            {/* Amount Details */}
                            <div className="mt-8">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Montants
                                </h3>
                                <div className="bg-gray-50 rounded-lg p-4">
                                    <dl className="grid grid-cols-1 gap-4">
                                        <div className="flex justify-between">
                                            <dt className="text-sm font-medium text-gray-500">Montant HT</dt>
                                            <dd className="text-sm text-gray-900">
                                                {new Intl.NumberFormat('fr-FR', {
                                                    style: 'currency',
                                                    currency: 'XAF'
                                                }).format(invoice.amount_ht)}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between border-t border-gray-200 pt-4">
                                            <dt className="text-sm font-medium text-gray-500">TVA ({invoice.client.tva_rate}%)</dt>
                                            <dd className="text-sm text-gray-900">
                                                {new Intl.NumberFormat('fr-FR', {
                                                    style: 'currency',
                                                    currency: 'XAF'
                                                }).format(invoice.tva)}
                                            </dd>
                                        </div>
                                        <div className="flex justify-between border-t border-gray-200 pt-4 font-bold">
                                            <dt className="text-sm text-gray-900">Total TTC</dt>
                                            <dd className="text-sm text-gray-900">
                                                {new Intl.NumberFormat('fr-FR', {
                                                    style: 'currency',
                                                    currency: 'XAF'
                                                }).format(invoice.amount_ttc)}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}