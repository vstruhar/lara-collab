import { usePage } from "@inertiajs/react";

export default function useAuthorization() {
  const {auth} = usePage().props;

  const can = (permission) => {
    return auth.user.permissions.includes(permission);
  };

  return {can};
}
