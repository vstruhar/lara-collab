import { router } from '@inertiajs/react';
import omit from 'lodash/omit';

export const redirectTo = (routeName, params = {}) => () => {
  router.get(route(routeName, params));
};

export const currentUrl = () => {
  return location.origin + location.pathname;
}

export const currentUrlParams = () => {
  const urlParams = new URLSearchParams(location.search);
  const params = {};

  for(const entry of urlParams.entries()) {
    params[entry[0]] = entry[1];
  }
  return params;
}

export const reloadWithQuery = (query) => {
  router.get(
    currentUrl(),
    { ...currentUrlParams(), ...query },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

export const reloadWithoutQueryParams = (excludeParams = []) => {
  router.get(
    currentUrl(),
    omit(currentUrlParams(), excludeParams),
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};
