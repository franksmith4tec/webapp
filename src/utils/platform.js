export function getPlatform() {
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;

    // Android detection
    if (/android/i.test(userAgent)) {
        return 'android';
    }

    // iOS detection
    if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
        return 'ios';
    }

    // Check if running in Capacitor
    if (window.Capacitor && window.Capacitor.getPlatform()) {
        return window.Capacitor.getPlatform();
    }

    // Default to web
    return 'web';
}

export const isMobilePlatform = () => {
    const platform = getPlatform();
    return platform === 'android' || platform === 'ios';
}