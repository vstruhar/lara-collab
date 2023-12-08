import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import Pagination from "@/components/Pagination";
import SearchInput from "@/components/SearchInput";
import TableHead from "@/components/TableHead";
import TableRowEmpty from "@/components/TableRowEmpty";
import Layout from "@/layouts/MainLayout";
import { redirectTo, reloadWithQuery } from "@/utils/route";
import { actionColumnVisibility, prepareColumns } from "@/utils/table";
import { usePage } from "@inertiajs/react";
import { Button, Grid, Group, Table } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
import TableRow from "./TableRow";

const InvoicesIndex = () => {
  const { items } = usePage().props;

  const columns = prepareColumns([
    { label: "Number", column: "number" },
    { label: "Status", column: "status" },
    { label: "Company", column: "name" },
    { label: "Note", column: "note" },
    { label: "Total", column: "amount" },
    { label: "Total with tax", column: "amount_with_tax" },
    { label: "Payment due", column: "due_date" },
    { label: "Created", column: "created_at" },
    {
      label: "Actions",
      sortable: false,
      visible: actionColumnVisibility("invoice"),
    },
  ]);

  let rows = items.data.length ? (
    items.data.map((item) => <TableRow key={item.id} item={item} />)
  ) : (
    <TableRowEmpty colSpan={columns.length} />
  );

  const search = (search) => reloadWithQuery({ search });
  const sort = (sort) => reloadWithQuery(sort);

  return (
    <>
      <Grid justify="space-between" align="center">
        <Grid.Col span="content">
          <Group>
            <SearchInput placeholder="Search invoices" search={search} />
            <ArchivedFilterButton />
          </Group>
        </Grid.Col>
        <Grid.Col span="content">
          {can("create invoice") && (
            <Button
              leftSection={<IconPlus size={14} />}
              radius="xl"
              onClick={() => redirectTo("invoices.create")}
            >
              Create
            </Button>
          )}
        </Grid.Col>
      </Grid>

      <Table.ScrollContainer miw={800} my="lg">
        <Table verticalSpacing="sm">
          <TableHead columns={columns} sort={sort} />
          <Table.Tbody>{rows}</Table.Tbody>
        </Table>
      </Table.ScrollContainer>

      <Pagination current={items.meta.current_page} pages={items.meta.last_page} />
    </>
  );
};

InvoicesIndex.layout = (page) => <Layout title="Invoices">{page}</Layout>;

export default InvoicesIndex;
