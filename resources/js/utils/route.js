import { router } from '@inertiajs/react';

export const redirectTo = (routeName, params = {}) => () => {
  router.get(route(routeName, params));
};

export const reloadWithQuery = (query) => {
  router.get(
    route(route().current()),
    { ...route().params, ...query },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};
