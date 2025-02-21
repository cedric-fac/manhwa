import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ArrowLeftIcon, CameraIcon } from 'lucide-react';

export default function Show({ auth, client, reading }) {
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
                        Détails du relevé - {client.name}
                    </h2>
                </div>
            }
        >
            <Head title={`Détails du relevé - ${client.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Reading Details */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Informations
                                    </h3>
                                    <dl className="grid grid-cols-1 gap-4">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Client</dt>
                                            <dd className="mt-1 text-sm text-gray-900">{client.name}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Valeur du compteur
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">{reading.value}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Date du relevé
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {new Date(reading.read_at).toLocaleDateString()}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">Statut</dt>
                                            <dd className="mt-1">
                                                {reading.synced ? (
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Synchronisé
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        En attente
                                                    </span>
                                                )}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                {/* Photo */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Photo du compteur
                                    </h3>
                                    {reading.photo_url ? (
                                        <div className="relative aspect-[4/3] w-full overflow-hidden rounded-lg bg-gray-100">
                                            <img
                                                src={reading.photo_url}
                                                alt="Photo du compteur"
                                                className="h-full w-full object-cover"
                                            />
                                        </div>
                                    ) : (
                                        <div className="flex aspect-[4/3] w-full items-center justify-center rounded-lg bg-gray-100">
                                            <CameraIcon className="h-12 w-12 text-gray-400" />
                                            <span className="ml-2 text-gray-500">Pas de photo</span>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}