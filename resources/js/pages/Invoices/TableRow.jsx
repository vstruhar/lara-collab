import TableRowActions from "@/components/TableRowActions";
import { Table, Text } from "@mantine/core";

export default function TableRow({ item }) {
  return (
    <Table.Tr key={item.id}>
      <Table.Td>
        <Text fz="sm" fw={500}>
          {item.number}
        </Text>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{item.client_company.name}</Text>
        <Text fz="xs" c="dimmed">
          Company
        </Text>
      </Table.Td>
      <Table.Td></Table.Td>
      {(can("edit invoice") || can("archive invoice") || can("restore invoice")) && (
        <Table.Td>
          <TableRowActions
            item={item}
            editRoute="invoices.edit"
            editPermission="edit invoice"
            archivePermission="archive invoice"
            restorePermission="restore invoice"
            archive={{
              route: "invoices.destroy",
              title: "Archive invoice",
              content: `Are you sure you want to archive this invoice?`,
              confirmLabel: "Archive",
            }}
            restore={{
              route: "invoices.restore",
              title: "Restore invoice",
              content: `Are you sure you want to restore this invoice?`,
              confirmLabel: "Restore",
            }}
          />
        </Table.Td>
      )}
    </Table.Tr>
  );
}
