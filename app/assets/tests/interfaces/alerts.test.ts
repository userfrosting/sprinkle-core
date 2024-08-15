import { describe, expect, test } from 'vitest'
import { type AlertInterface, AlertStyle } from '../../interfaces';

describe('AlertInterface', () => {
    test('should create an alert with title and description', () => {
        const alert: AlertInterface = {
            title: 'Test Alert',
            description: 'This is a test alert',
        };

        expect(alert.title).toBe('Test Alert');
        expect(alert.description).toBe('This is a test alert');
    });

    test('should create an alert with style', () => {
        const alert: AlertInterface = {
            style: AlertStyle.Success,
        };

        expect(alert.style).toBe(AlertStyle.Success);
    });

    test('should create an alert with close button and hide icon', () => {
        const alert: AlertInterface = {
            closeBtn: true,
            hideIcon: true,
        };

        expect(alert.closeBtn).toBe(true);
        expect(alert.hideIcon).toBe(true);
    });
});

describe('AlertStyle', () => {
    test('should have the correct values', () => {
        expect(AlertStyle.Primary).toBe('Primary');
        expect(AlertStyle.Success).toBe('Success');
        expect(AlertStyle.Warning).toBe('Warning');
        expect(AlertStyle.Danger).toBe('Danger');
    });
});