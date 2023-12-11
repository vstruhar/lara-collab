import TableRowActions from "@/components/TableRowActions";
import { money } from "@/utils/currency";
import { date, time } from "@/utils/datetime";
import { download } from "@/utils/file";
import { redirectTo } from "@/utils/route";
import { ActionIcon, Anchor, HoverCard, Table, Text, Tooltip, rem } from "@mantine/core";
import { IconFileDownload, IconNotes, IconNotesOff, IconPrinter } from "@tabler/icons-react";
import axios from "axios";
import dayjs from "dayjs";
import printJS from "print-js";
import { useState } from "react";
import StatusDropdown from "./StatusDropdown";

export default function TableRow({ item }) {
  const [printLoading, setPrintLoading] = useState(false);

  const downloadPdf = () => {
    axios.get(route("invoices.download", item.id), { responseType: "blob" }).then(({ data }) => {
      download(data, item.filename.slice(5), "application/pdf");
    });
  };

  const printPdf = () => {
    printJS({
      printable: route("invoices.pdf", item.id),
      type: "pdf",
      onLoadingStart: () => setPrintLoading(true),
      onLoadingEnd: () => setTimeout(() => setPrintLoading(false), 500),
    });
  };

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
          <HoverCard width={280} shadow="md" withArrow>
            <HoverCard.Target>
              <IconNotes />
            </HoverCard.Target>
            <HoverCard.Dropdown>
              <Text size="sm">{item.note}</Text>
            </HoverCard.Dropdown>
          </HoverCard>
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
          c={dayjs().isAfter(item.due_date) && item.status !== "paid" ? "red" : "inherit"}
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
          >
            {can("download invoice") && (
              <ActionIcon variant="subtle" color="blue" onClick={() => downloadPdf()}>
                <IconFileDownload style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
              </ActionIcon>
            )}

            {can("print invoice") && (
              <ActionIcon
                variant="subtle"
                color="blue"
                loading={printLoading}
                onClick={() => printPdf()}
              >
                <IconPrinter style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
              </ActionIcon>
            )}
          </TableRowActions>
        </Table.Td>
      )}
    </Table.Tr>
  );
}
