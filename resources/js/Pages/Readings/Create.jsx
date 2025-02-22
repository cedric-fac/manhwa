import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { MeterReadingInput } from '@/Components/MeterReadingInput';

export default function Create({ auth, client }) {
    const { data, setData, post, processing, errors } = useForm({
        value: '',
        read_at: new Date().toISOString().split('T')[0],
        photo: null,
        ocr_data: null
    });

    const handleReadingCapture = (value, photoFile, ocrData = null) => {
        setData(prev => ({
            ...prev,
            value: value.toString(),
            photo: photoFile,
            ocr_data: ocrData
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('value', data.value);
        formData.append('read_at', data.read_at);
        if (data.photo) {
            formData.append('photo', data.photo);
        }
        if (data.ocr_data) {
            formData.append('ocr_data', JSON.stringify(data.ocr_data));
        }

        post(route('readings.store', client.id), {
            data: formData,
            forceFormData: true
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                    Nouveau Relevé - {client.name}
                </h2>
            }
        >
            <Head title={`Nouveau Relevé - ${client.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <form onSubmit={handleSubmit} className="p-6 space-y-6">
                            <div>
                                <MeterReadingInput
                                    onReadingCapture={handleReadingCapture}
                                    isUploading={processing}
                                    error={errors.value || errors.photo}
                                />
                            </div>

                            <div>
                                <label htmlFor="read_at" className="block text-sm font-medium text-gray-700">
                                    Date du relevé
                                </label>
                                <input
                                    type="date"
                                    id="read_at"
                                    name="read_at"
                                    value={data.read_at}
                                    onChange={e => setData('read_at', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                                {errors.read_at && (
                                    <p className="mt-2 text-sm text-red-600">
                                        {errors.read_at}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-end">
                                <button
                                    type="submit"
                                    disabled={processing || !data.value || !data.photo}
                                    className="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:opacity-50"
                                >
                                    {processing ? 'Envoi...' : 'Enregistrer'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}