const useTable = () => {
  const prepareColumns = (columns) => {
    return columns.filter((c) => c.visible !== false);
  };

  const actionColumnVisibility = (name) => {
      return (can(`edit ${name}`) && !route().params.archived) ||
        (can(`archive ${name}`) && !route().params.archived) ||
        (can(`restore ${name}`) && route().params.archived);
  };

  return {prepareColumns, actionColumnVisibility};
};

export default useTable;
