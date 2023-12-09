import { produce } from 'immer';
import { create } from 'zustand';

const useNavigationStore = create((set) => ({
  items: [],
  setItems: (items) => set(() => ({ items: [...items] })),
  toggle: (label) => {
    return set(produce(state => {
      state.items.forEach(item => {
        if (item.opened !== undefined) {
          if (item.label === label) {
            const index = state.items.findIndex(i => i.label === label);
            state.items[index].opened = !state.items[index].opened;
          } else {
            item.opened = false;
          }
        }
      });
    }));
  },
  active: (label, isSubItem) => {
    return set(produce(state => {
      state.items.forEach(item => {
        const hasLinks = Array.isArray(item.links);

        if (hasLinks) {
          let hasActive = false;

          item.links.forEach(subItem => {
            if (subItem.label === label && isSubItem) {
              subItem.active = true;
              hasActive = true;
            } else {
              subItem.active = false;
            }
          });

          item.active = hasActive;
          item.opened = hasActive;
        } else {
          if (item.label === label && !isSubItem) {
            item.active = true;
          } else {
            item.active = false;
          }
        }
      });
    }));
  },
}));

export default useNavigationStore;
