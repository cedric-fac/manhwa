import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { CheckCircle2, ArrowLeft } from 'lucide-react';

export default function Review({ auth, training_data }) {
    const [showOriginal, setShowOriginal] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        corrected_text: training_data.original_text,
        feedback: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('ocr.update', training_data.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center">
                    <a 
                        href={route('ocr.dashboard')} 
                        className="mr-4 text-gray-600 hover:text-gray-900"
                    >
                        <ArrowLeft className="w-5 h-5" />
                    </a>
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Révision OCR
                    </h2>
                </div>
            }
        >
            <Head title="Révision OCR" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Image Preview */}
                            <div>
                                <h3 className="text-lg font-medium text-gray-900 mb-4">Image</h3>
                                <img
                                    src={training_data.image_url}
                                    alt="Meter Reading"
                                    className="w-full rounded-lg shadow-lg"
                                />
                            </div>

                            {/* OCR Data and Form */}
                            <div>
                                <div className="mb-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Texte Original
                                    </h3>
                                    <div className="flex items-center gap-2 mb-2">
                                        <span className={`px-2 py-1 text-sm font-semibold rounded-full ${
                                            training_data.confidence >= 80 
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            Confiance: {Math.round(training_data.confidence)}%
                                        </span>
                                        <button
                                            type="button"
                                            className="text-sm text-blue-600 hover:text-blue-800"
                                            onClick={() => setShowOriginal(!showOriginal)}
                                        >
                                            {showOriginal ? 'Masquer' : 'Voir'} le texte original
                                        </button>
                                    </div>
                                    {showOriginal && (
                                        <p className="p-4 bg-gray-50 rounded-md font-mono text-sm">
                                            {training_data.original_text}
                                        </p>
                                    )}
                                </div>

                                <form onSubmit={handleSubmit} className="space-y-6">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">
                                            Correction
                                        </label>
                                        <input
                                            type="text"
                                            value={data.corrected_text}
                                            onChange={e => setData('corrected_text', e.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            required
                                        />
                                        {errors.corrected_text && (
                                            <p className="mt-1 text-sm text-red-600">
                                                {errors.corrected_text}
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">
                                            Feedback (optionnel)
                                        </label>
                                        <textarea
                                            value={data.feedback}
                                            onChange={e => setData('feedback', e.target.value)}
                                            rows={3}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Commentaires sur la qualité de l'OCR..."
                                        />
                                    </div>

                                    <div className="flex items-center justify-end">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                        >
                                            <CheckCircle2 className="w-5 h-5 mr-2" />
                                            Valider la correction
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}