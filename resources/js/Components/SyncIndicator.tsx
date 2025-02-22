import React from 'react';
import { useOffline } from '@/hooks/useOffline';

export function SyncIndicator(): React.ReactElement | null {
    const { isOnline, isSyncing, pendingItems } = useOffline();

    if (!pendingItems && isOnline && !isSyncing) return null;

    return (
        <div className="fixed bottom-4 right-4 bg-white rounded-lg shadow-lg p-4 max-w-sm">
            <div className="flex items-center space-x-2">
                <div 
                    className={`w-2 h-2 rounded-full ${
                        isOnline ? 'bg-green-500' : 'bg-red-500'
                    }`}
                    aria-hidden="true"
                />
                <span className="text-sm font-medium text-gray-700">
                    {!isOnline 
                        ? 'Hors ligne'
                        : isSyncing 
                        ? 'Synchronisation...'
                        : pendingItems 
                        ? `${pendingItems} relevé${pendingItems > 1 ? 's' : ''} en attente`
                        : 'Synchronisé'}
                </span>
            </div>
        </div>
    );
}