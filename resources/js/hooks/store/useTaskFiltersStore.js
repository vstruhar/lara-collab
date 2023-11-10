import { currentUrlParams, reloadWithQuery, reloadWithoutQueryParams } from '@/utils/route';
import { produce } from "immer";
import { create } from 'zustand';

const params = currentUrlParams();

const useTaskFiltersStore = create((set) => ({
  filters: {
    groups: params.groups || [],
    assignees: params.assignees || [],
    due_date: {
      not_set: params.not_set || 0,
      overdue: params.overdue || 0,
    },
    labels: params.labels || [],
  },
  toggleArrayFilter: (field, id) => {
    return set(
      produce((state) => {
        const index = state.filters[field].findIndex((i) => i === id);

        if (index !== -1) {
          state.filters[field].splice(index, 1);
        } else {
          state.filters[field].push(id);
        }
        reloadWithQuery({ [field]: state.filters[field] });
      }),
    );
  },
  toggleObjectFilter: (field, property) => {
    return set(
      produce((state) => {
        if (state.filters[field][property] === 0) {
          state.filters[field][property] = 1;
          reloadWithQuery({ [property]: 1 });
        } else {
          state.filters[field][property] = 0;
          reloadWithoutQueryParams([property]);
        }
      }),
    );
  },
}));

export default useTaskFiltersStore;
