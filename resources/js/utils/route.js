import {router} from '@inertiajs/react';

export const redirectWithQuery = (query) => {
  router.get(
    route(route().current()),
    { ...route().params, ...query },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};
