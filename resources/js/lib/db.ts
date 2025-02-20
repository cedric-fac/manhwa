import Dexie, { Table } from 'dexie';

// Define interfaces for our models
interface Reading {
    id?: number;
    client_id: number;
    value: number;
    photo_url?: string;
    read_at: string;
    synced: boolean;
}

export class AppDatabase extends Dexie {
    readings!: Table<Reading>;

    constructor() {
        super('ElectricBillingDB');
        
        this.version(1).stores({
            readings: '++id, client_id, value, synced'
        });
    }
}

// Create database instance
export const db = new AppDatabase();

// Function to sync offline readings with server
export async function syncReadings() {
    if (!navigator.onLine) return;

    const pendingReadings = await db.readings
        .where('synced')
        .equals(false)
        .toArray();

    for (const reading of pendingReadings) {
        try {
            const response = await fetch('/api/readings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(reading),
            });

            if (response.ok) {
                await db.readings.update(reading.id!, { synced: true });
            }
        } catch (error) {
            console.error('Failed to sync reading:', error);
        }
    }
}

// Function to add a new reading
export async function addReading(reading: Omit<Reading, 'id' | 'synced'>) {
    const newReading = {
        ...reading,
        synced: navigator.onLine,
    };

    const id = await db.readings.add(newReading);

    if (navigator.onLine) {
        try {
            await syncReadings();
        } catch (error) {
            // If sync fails, mark as unsynced
            await db.readings.update(id, { synced: false });
        }
    }

    return id;
}

// Setup auto-sync when coming online
window.addEventListener('online', () => {
    syncReadings().catch(console.error);
});