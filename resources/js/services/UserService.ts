import { User as UserType } from '@/types';

export default class UserService {
    user: UserType;

    constructor(user: UserType) {
        this.user = user;
    }

    getInitials(): string {
        if (!this.user.name.includes(" ")) {
            return this.user.name.slice(0, 2).toUpperCase();
        }
        const [firstname, lastname] = this.user.name.split(" ");

        if (!lastname) {
            return firstname.slice(0, 2).toUpperCase();
        }

        return (firstname[0] + lastname[0]).toUpperCase();
    }
}
