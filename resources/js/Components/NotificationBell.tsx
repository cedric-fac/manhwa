import React, { useState, useEffect } from 'react';
import { Bell } from 'lucide-react';
import { Link } from '@inertiajs/react';
import route from 'ziggy-js';

interface Notification {
    id: string;
    type: string;
    data: {
        training_data_id: number;
        client_id: number;
        confidence: number;
        original_text: string;
    };
    read_at: string | null;
    created_at: string;
}

interface Props {
    className?: string;
}

export function NotificationBell({ className }: Props) {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [showDropdown, setShowDropdown] = useState(false);

    useEffect(() => {
        // Fetch notifications on component mount
        fetchNotifications();

        // Set up polling for new notifications
        const interval = setInterval(fetchNotifications, 30000); // Every 30 seconds

        // Click outside to close dropdown
        const handleClickOutside = (event: MouseEvent) => {
            if (showDropdown && !(event.target as Element).closest('.notification-bell')) {
                setShowDropdown(false);
            }
        };

        document.addEventListener('click', handleClickOutside);

        return () => {
            clearInterval(interval);
            document.removeEventListener('click', handleClickOutside);
        };
    }, [showDropdown]);

    const fetchNotifications = async () => {
        try {
            const response = await fetch('/notifications/unread');
            const data = await response.json();
            setNotifications(data.notifications);
        } catch (error) {
            console.error('Failed to fetch notifications:', error);
        }
    };

    const markAsRead = async (id: string) => {
        try {
            await fetch(`/notifications/${id}/read`, { method: 'POST' });
            setNotifications(notifications.filter(n => n.id !== id));
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    return (
        <div className={`relative notification-bell ${className}`}>
            <button
                onClick={() => setShowDropdown(!showDropdown)}
                className="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
                aria-label="Notifications"
            >
                <Bell className="w-6 h-6" />
                {notifications.length > 0 && (
                    <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full">
                        {notifications.length}
                    </span>
                )}
            </button>

            {showDropdown && notifications.length > 0 && (
                <div className="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg z-50">
                    <div className="py-2">
                        <div className="px-4 py-2 text-sm font-medium text-gray-700 border-b">
                            Révisions OCR en attente
                        </div>
                        <div className="max-h-96 overflow-y-auto">
                            {notifications.map((notification) => (
                                <div
                                    key={notification.id}
                                    className="px-4 py-3 hover:bg-gray-50 border-b last:border-b-0"
                                >
                                    <Link
                                        href={route('ocr.review', notification.data.training_data_id)}
                                        onClick={() => markAsRead(notification.id)}
                                        className="block"
                                    >
                                        <div className="flex items-start">
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-gray-900">
                                                    Confiance: {Math.round(notification.data.confidence)}%
                                                </p>
                                                <p className="text-sm text-gray-600 truncate">
                                                    Texte: {notification.data.original_text}
                                                </p>
                                                <p className="text-xs text-gray-500 mt-1">
                                                    {new Date(notification.created_at).toLocaleString()}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                            ))}
                        </div>
                        {notifications.length > 0 && (
                            <div className="px-4 py-2 text-center">
                                <Link
                                    href={route('ocr.dashboard')}
                                    className="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    Voir toutes les révisions
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}