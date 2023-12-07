import TableRowActions from "@/components/TableRowActions";
import { money } from "@/utils/currency";
import { date, time } from "@/utils/datetime";
import { redirectTo } from "@/utils/route";
import { Anchor, Table, Text, Tooltip } from "@mantine/core";
import { IconNotes, IconNotesOff } from "@tabler/icons-react";
import dayjs from "dayjs";
import StatusDropdown from "./StatusDropdown";

export default function TableRow({ item }) {
  return (
    <Table.Tr key={item.id}>
      <Table.Td>
        <Text fz="md" fw={700}>
          {item.number}
        </Text>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">
          <StatusDropdown invoiceId={item.id} status={item.status} />
        </Text>
      </Table.Td>
      <Table.Td>
        <Anchor onClick={() => redirectTo("clients.companies.edit", item.client_company.id)}>
          <Text fz="md" fw={600}>
            {item.client_company.name}
          </Text>
        </Anchor>
        <Text fz="xs" c="dimmed">
          Client company
        </Text>
      </Table.Td>
      <Table.Td>
        {item.note ? (
          <Tooltip label={item.note} openDelay={250} withArrow>
            <IconNotes />
          </Tooltip>
        ) : (
          <IconNotesOff opacity={0.3} />
        )}
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{money(item.amount, item.client_company.currency.code)}</Text>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{money(item.amount_with_tax, item.client_company.currency.code)}</Text>
      </Table.Td>
      <Table.Td>
        <Text
          fz="sm"
          c={dayjs().isAfter(item.due_date) && item.status !== "paid" ? "red.8" : "inherit"}
        >
          {date(item.due_date)}
        </Text>
      </Table.Td>
      <Table.Td>
        <Tooltip label={time(item.created_at)} openDelay={500} withArrow>
          <Text fz="sm" component="span" inline>
            {date(item.created_at)}
          </Text>
        </Tooltip>
      </Table.Td>
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
