import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { CheckCircle } from 'lucide-react';

export default function Review({ auth, trainingData }) {
    const { data, setData, post, processing, errors } = useForm({
        corrected_text: trainingData.original_text,
        feedback: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('ocr.update', trainingData.id));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Révision OCR</h2>}
        >
            <Head title="Révision OCR" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {/* Client Info */}
                            <div className="mb-6">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Client: {trainingData.reading.client.name}
                                </h3>
                                <p className="text-sm text-gray-600">
                                    Confiance OCR: {Math.round(trainingData.confidence)}%
                                </p>
                            </div>

                            {/* Image Preview */}
                            <div className="mb-6">
                                <h4 className="text-md font-medium mb-2">Image du compteur</h4>
                                <div className="border rounded-lg overflow-hidden">
                                    <img
                                        src={trainingData.image_url}
                                        alt="Meter Reading"
                                        className="w-full max-w-2xl mx-auto"
                                    />
                                </div>
                            </div>

                            <form onSubmit={submit}>
                                {/* Original Text */}
                                <div className="mb-4">
                                    <label className="block text-sm font-medium text-gray-700">
                                        Texte original
                                    </label>
                                    <div className="mt-1">
                                        <input
                                            type="text"
                                            readOnly
                                            value={trainingData.original_text}
                                            className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md bg-gray-50"
                                        />
                                    </div>
                                </div>

                                {/* Corrected Text */}
                                <div className="mb-4">
                                    <label htmlFor="corrected_text" className="block text-sm font-medium text-gray-700">
                                        Texte corrigé
                                    </label>
                                    <div className="mt-1">
                                        <input
                                            type="text"
                                            name="corrected_text"
                                            id="corrected_text"
                                            value={data.corrected_text}
                                            onChange={e => setData('corrected_text', e.target.value)}
                                            className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        />
                                    </div>
                                    {errors.corrected_text && (
                                        <p className="mt-1 text-sm text-red-600">{errors.corrected_text}</p>
                                    )}
                                </div>

                                {/* Feedback */}
                                <div className="mb-4">
                                    <label htmlFor="feedback" className="block text-sm font-medium text-gray-700">
                                        Commentaire (optionnel)
                                    </label>
                                    <div className="mt-1">
                                        <textarea
                                            id="feedback"
                                            name="feedback"
                                            rows={3}
                                            value={data.feedback}
                                            onChange={e => setData('feedback', e.target.value)}
                                            className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                        />
                                    </div>
                                </div>

                                {/* Submit Button */}
                                <div className="mt-6">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                    >
                                        <CheckCircle className="mr-2 h-4 w-4" />
                                        Valider la correction
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}