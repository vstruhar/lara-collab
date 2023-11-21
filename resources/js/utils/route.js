import { router } from '@inertiajs/react';
import omit from 'lodash/omit';
import pick from 'lodash/pick';
import queryString from 'query-string';

export const redirectTo = (routeName, params = {}) => () => {
  router.get(route(routeName, params));
};

export const currentUrl = () => {
  return location.origin + location.pathname;
}

export const currentUrlParams = () => {
  return queryString.parse(location.search, {
    arrayFormat: 'bracket',
    parseBooleans: true,
    parseNumbers: true,
  });
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

export const reloadWithoutQueryParams = ({exclude, keep}) => {
  let params = currentUrlParams();

  if(exclude) {
    params = omit(currentUrlParams(), exclude);
  } else if(keep) {
    params = pick(currentUrlParams(), keep);
  }

  router.get(
    currentUrl(),
    params,
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
};

export const replaceUrlWithoutReload = (url) => {
  window.history.replaceState(
    {},
    "",
    url + location.search,
  );
};
