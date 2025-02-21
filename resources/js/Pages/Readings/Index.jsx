import { useState, useRef } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { CameraIcon, SaveIcon, WifiOffIcon } from 'lucide-react';
import { db } from '@/lib/db';

export default function Index({ auth, client, readings }) {
    const [previewUrl, setPreviewUrl] = useState(null);
    const [isOffline, setIsOffline] = useState(!navigator.onLine);
    const fileInputRef = useRef(null);

    const { data, setData, post, processing, reset, errors } = useForm({
        value: '',
        read_at: new Date().toISOString().split('T')[0],
        photo: null,
    });

    // Monitor online status
    window.addEventListener('online', () => setIsOffline(false));
    window.addEventListener('offline', () => setIsOffline(true));

    const handlePhotoChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('photo', file);
            const reader = new FileReader();
            reader.onload = (e) => setPreviewUrl(e.target.result);
            reader.readAsDataURL(file);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (isOffline) {
            // Store reading locally
            try {
                const photoBase64 = previewUrl ? previewUrl.split(',')[1] : null;
                await db.readings.add({
                    client_id: client.id,
                    value: parseFloat(data.value),
                    read_at: data.read_at,
                    photo: photoBase64,
                    synced: false,
                });

                reset();
                setPreviewUrl(null);
                alert('Relevé sauvegardé localement. Il sera synchronisé quand vous serez en ligne.');
            } catch (error) {
                console.error('Failed to save reading locally:', error);
                alert('Erreur lors de la sauvegarde locale du relevé.');
            }
        } else {
            // Send to server
            const formData = new FormData();
            formData.append('value', data.value);
            formData.append('read_at', data.read_at);
            if (data.photo) {
                formData.append('photo', data.photo);
            }

            post(route('readings.store', client.id), {
                data: formData,
                onSuccess: () => {
                    reset();
                    setPreviewUrl(null);
                },
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Relevés de {client.name}
                    </h2>
                    {isOffline && (
                        <div className="flex items-center text-yellow-600">
                            <WifiOffIcon className="w-5 h-5 mr-2" />
                            Mode hors ligne
                        </div>
                    )}
                </div>
            }
        >
            <Head title={`Relevés - ${client.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        {/* New Reading Form */}
                        <form onSubmit={handleSubmit} className="mb-8 space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Valeur du compteur
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        value={data.value}
                                        onChange={e => setData('value', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    {errors.value && (
                                        <p className="mt-1 text-sm text-red-600">{errors.value}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700">
                                        Date du relevé
                                    </label>
                                    <input
                                        type="date"
                                        value={data.read_at}
                                        onChange={e => setData('read_at', e.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    {errors.read_at && (
                                        <p className="mt-1 text-sm text-red-600">{errors.read_at}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700">
                                    Photo du compteur
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
                                    onChange={handlePhotoChange}
                                    ref={fileInputRef}
                                    className="hidden"
                                />
                                <div className="mt-1 flex items-center">
                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        <CameraIcon className="w-5 h-5 mr-2" />
                                        Prendre une photo
                                    </button>
                                </div>
                                {previewUrl && (
                                    <div className="mt-2">
                                        <img
                                            src={previewUrl}
                                            alt="Aperçu"
                                            className="h-48 w-auto object-cover rounded-lg"
                                        />
                                    </div>
                                )}
                                {errors.photo && (
                                    <p className="mt-1 text-sm text-red-600">{errors.photo}</p>
                                )}
                            </div>

                            <div className="mt-4">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                >
                                    <SaveIcon className="w-5 h-5 mr-2" />
                                    Enregistrer le relevé
                                </button>
                            </div>
                        </form>

                        {/* Readings List */}
                        <div className="mt-8">
                            <h3 className="text-lg font-medium text-gray-900">Historique des relevés</h3>
                            <div className="mt-4">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valeur
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Photo
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Statut
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {readings.data.map((reading) => (
                                            <tr key={reading.id}>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {new Date(reading.read_at).toLocaleDateString()}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {reading.value}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {reading.photo_url && (
                                                        <a
                                                            href={reading.photo_url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="text-blue-600 hover:text-blue-900"
                                                        >
                                                            Voir la photo
                                                        </a>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    {reading.synced ? (
                                                        <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            Synchronisé
                                                        </span>
                                                    ) : (
                                                        <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                            En attente
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}