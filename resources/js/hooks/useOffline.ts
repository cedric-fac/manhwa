import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
import { db } from '@/lib/db';

interface UseOfflineResult {
    isOnline: boolean;
    isSyncing: boolean;
    pendingItems: number;
    sync: () => Promise<void>;
}

export function useOffline(): UseOfflineResult {
    const [isOnline, setIsOnline] = useState(navigator.onLine);
    const [isSyncing, setIsSyncing] = useState(false);
    const [pendingItems, setPendingItems] = useState(0);

    useEffect(() => {
        async function updatePendingCount() {
            const count = await db.readings
                .where({ synced: false })
                .count();
            setPendingItems(count);
        }

        // Initial check
        updatePendingCount();

        // Update online status
        function handleOnline() {
            setIsOnline(true);
            handleSync();
        }

        function handleOffline() {
            setIsOnline(false);
        }

        // Handle sync messages from service worker
        function handleSyncMessage(event: MessageEvent) {
            if (event.data?.type === 'SYNC_COMPLETE') {
                setIsSyncing(false);
                if (event.data.success) {
                    // Refresh the current page to show updated data
                    router.reload({ preserveUrl: true });
                }
            }
        }

        // Register listeners
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        navigator.serviceWorker?.addEventListener('message', handleSyncMessage);

        // Watch for changes in IndexedDB
        db.readings.hook('creating', () => {
            updatePendingCount();
        });

        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
            navigator.serviceWorker?.removeEventListener('message', handleSyncMessage);
        };
    }, []);

    // Function to trigger manual sync
    const handleSync = async () => {
        if (!navigator.onLine || isSyncing) return;

        setIsSyncing(true);
        try {
            await db.syncReadings();
            const count = await db.readings
                .where({ synced: false })
                .count();
            setPendingItems(count);
        } catch (error) {
            console.error('Sync failed:', error);
        } finally {
            setIsSyncing(false);
        }
    };

    return {
        isOnline,
        isSyncing,
        pendingItems,
        sync: handleSync
    };
}