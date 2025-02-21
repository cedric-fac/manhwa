import { useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { SaveIcon } from 'lucide-react';

export default function Form({ auth, client }) {
    const { data, setData, post, put, processing, errors } = useForm({
        name: client?.name || '',
        phone: client?.phone || '+237',
        address: client?.address || '',
        tva_rate: client?.tva_rate || 19.25,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (client) {
            put(route('clients.update', client.id));
        } else {
            post(route('clients.store'));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    {client ? 'Modifier le Client' : 'Nouveau Client'}
                </h2>
            }
        >
            <Head title={client ? 'Modifier le Client' : 'Nouveau Client'} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6 space-y-6">
                            <div>
                                <InputLabel htmlFor="name" value="Nom" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    className="mt-1 block w-full"
                                    required
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="phone" value="Téléphone" />
                                <TextInput
                                    id="phone"
                                    type="tel"
                                    value={data.phone}
                                    onChange={e => setData('phone', e.target.value)}
                                    className="mt-1 block w-full"
                                    required
                                    placeholder="+237XXXXXXXXX"
                                />
                                <InputError message={errors.phone} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="address" value="Adresse" />
                                <TextInput
                                    id="address"
                                    type="text"
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                    className="mt-1 block w-full"
                                    required
                                />
                                <InputError message={errors.address} className="mt-2" />
                            </div>

                            <div>
                                <InputLabel htmlFor="tva_rate" value="Taux TVA (%)" />
                                <TextInput
                                    id="tva_rate"
                                    type="number"
                                    step="0.01"
                                    value={data.tva_rate}
                                    onChange={e => setData('tva_rate', e.target.value)}
                                    className="mt-1 block w-full"
                                    required
                                />
                                <InputError message={errors.tva_rate} className="mt-2" />
                            </div>

                            <div className="flex items-center justify-end gap-4">
                                <button
                                    type="button"
                                    onClick={() => window.history.back()}
                                    className="px-4 py-2 text-gray-700 hover:text-gray-900"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <SaveIcon className="w-4 h-4" />
                                    {client ? 'Mettre à jour' : 'Créer'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}