export const prepareColumns = (columns) => {
  return columns.filter((c) => c.visible !== false);
};

export const actionColumnVisibility = (name) => {
  return (can(`edit ${name}`) && !route().params.archived) ||
    (can(`archive ${name}`) && !route().params.archived) ||
    (can(`restore ${name}`) && route().params.archived);
};
