import { usePage } from "@inertiajs/react";

const useAuthorization = () => {
  const {auth} = usePage().props;

  const can = (permission) => {
    return auth.user.permissions.includes(permission);
  };

  return {can};
};

export default useAuthorization;
