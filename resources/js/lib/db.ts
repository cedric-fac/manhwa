import Dexie, { Table } from 'dexie';
import { router } from '@inertiajs/react';

interface IReading {
    id?: number;
    client_id: number;
    value: number;
    photo?: File | string;
    photo_url?: string;
    read_at: string;
    synced: boolean;
    sync_error?: string;
}

interface IClient {
    id: number;
    name: string;
    phone: string;
    email?: string;
    address: string;
    tva_rate: number;
}

class AppDatabase extends Dexie {
    readings!: Table<IReading>;
    clients!: Table<IClient>;

    constructor() {
        super('ElectricBillingDB');
        this.version(1).stores({
            readings: '++id, client_id, read_at', // Remove synced from index as it's not indexable
            clients: '++id, name, phone'
        });
    }

    async syncReadings() {
        const unsynced = await this.readings
            .where('synced')
            .equals(false)
            .toArray();

        for (const reading of unsynced) {
            try {
                const formData = new FormData();
                formData.append('value', reading.value.toString());
                formData.append('read_at', reading.read_at);
                formData.append('client_id', reading.client_id.toString());
                
                if (reading.photo instanceof File) {
                    formData.append('photo', reading.photo);
                }

                const response = await fetch(`/clients/${reading.client_id}/readings`, {
                    method: 'POST',
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Sync failed');
                }

                // Update local record
                await this.readings.update(reading.id!, {
                    synced: true,
                    sync_error: null
                });

            } catch (error) {
                console.error('Sync error:', error);
                await this.readings.update(reading.id!, {
                    sync_error: error instanceof Error ? error.message : 'Unknown error'
                });
            }
        }
    }

    async storeReading(reading: Omit<IReading, 'id' | 'synced'>) {
        const id = await this.readings.add({
            ...reading,
            synced: false
        });

        // Try to sync immediately if online
        if (navigator.onLine) {
            await this.syncReadings();
        }

        return id;
    }

    async refreshClients() {
        if (!navigator.onLine) return;

        try {
            const response = await fetch('/api/clients');
            if (!response.ok) throw new Error('Failed to fetch clients');
            
            const clients = await response.json();
            
            // Clear existing clients
            await this.clients.clear();
            // Store new clients
            await this.clients.bulkAdd(clients);
        } catch (error) {
            console.error('Failed to refresh clients:', error);
        }
    }
}

export const db = new AppDatabase();

// Setup sync listeners
window.addEventListener('online', () => {
    db.syncReadings().catch(console.error);
    db.refreshClients().catch(console.error);
});

// Initial sync when the app loads
if (navigator.onLine) {
    db.syncReadings().catch(console.error);
    db.refreshClients().catch(console.error);
}

export type { IReading, IClient };