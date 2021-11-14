export class AuthService {
    public auth() {
        const info = JSON.parse(localStorage.getItem('user') ?? '');
        if (info && info.accessToken) {
            return info
        }

        return  {};
    }
}