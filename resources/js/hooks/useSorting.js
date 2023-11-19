import { useEffect, useState } from "react";

export default function useSorting(sort) {
  const [sortBy, setSortBy] = useState(null);
  const [reverseSortDirection, setReverseSortDirection] = useState(false);

  const setSorting = (field) => {
    const reversed = field === sortBy ? !reverseSortDirection : false;
    setReverseSortDirection(reversed);
    setSortBy(field);

    sort({sort: {[field]: reversed ? 'desc' : 'asc'}});
  };

  useEffect(() => {
    const currentSort = route().params.sort;

    if (currentSort) {
      const key = Object.keys(currentSort)[0];
      setSortBy(key);
      setReverseSortDirection(currentSort[key] === 'desc');
    }
  }, []);

  return [sortBy, reverseSortDirection, setSorting];
}
