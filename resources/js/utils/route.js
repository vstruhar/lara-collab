import { router } from '@inertiajs/react';
import omit from 'lodash/omit';

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

export const reloadWithoutQueryParams = (excludeParams = []) => {
  router.get(
    route(route().current()),
    omit(route().params, excludeParams),
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};
