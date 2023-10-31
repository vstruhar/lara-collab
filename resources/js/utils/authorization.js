import { usePage } from "@inertiajs/react";

export const can = (permission) => {
  return usePage().props.auth.user.permissions.includes(permission);
};
