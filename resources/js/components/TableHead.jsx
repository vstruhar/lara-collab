import { Table } from "@mantine/core";
import TableHeaderCell from "./TableHeaderCell";
import useSorting from "@/hooks/useSorting";

export default function TableHead({ columns, sort }) {
  const [sortBy, reverseSortDirection, setSorting] = useSorting(sort);

  return (
    <Table.Thead>
      <Table.Tr>
        {columns.map((item) => (
          <TableHeaderCell
            key={item.column || item.label}
            column={item.column}
            sorted={sortBy === item.column}
            reversed={reverseSortDirection}
            sortable={item.sortable}
            onSort={() => setSorting(item.column)}
          >
            {item.label}
          </TableHeaderCell>
        ))}
      </Table.Tr>
    </Table.Thead>
  );
}
