import { Worker, WorkerOptions, Page } from 'tesseract.js';

declare module 'tesseract.js' {
    interface ILogger {
        (log: {
            status: string;
            progress?: number;
            userJobId?: string;
        }): void;
    }

    interface WorkerParams extends WorkerOptions {
        logger?: ILogger;
    }
}