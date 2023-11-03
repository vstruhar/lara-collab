import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import Pagination from "@/components/Pagination";
import SearchInput from "@/components/SearchInput";
import TableHead from "@/components/TableHead";
import TableRowEmpty from "@/components/TableRowEmpty";
import Layout from "@/layouts/MainLayout";
import {
  actionColumnVisibility,
  prepareColumns,
} from "@/services/TableService";
import { redirectTo, reloadWithQuery } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import { Button, Grid, Group, Table } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
import TableRow from "./TableRow";

const LabelsIndex = () => {
  const { items } = usePage().props;

  const columns = prepareColumns([
    { label: "Color", sortable: false },
    { label: "Name", column: "name" },
    {
      label: "Actions",
      sortable: false,
      visible: actionColumnVisibility("label"),
    },
  ]);

  const rows = items.data.length ? (
    items.data.map((label) => <TableRow item={label} key={label.id} />)
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
            <SearchInput placeholder="Search labels" search={search} />
            <ArchivedFilterButton />
          </Group>
        </Grid.Col>
        <Grid.Col span="content">
          {can("create label") && (
            <Button
              leftSection={<IconPlus size={14} />}
              radius="xl"
              onClick={redirectTo("settings.labels.create")}
            >
              Create
            </Button>
          )}
        </Grid.Col>
      </Grid>

      <Table.ScrollContainer maw={500} my="lg">
        <Table verticalSpacing="sm">
          <TableHead columns={columns} sort={sort} />
          <Table.Tbody>{rows}</Table.Tbody>
        </Table>
      </Table.ScrollContainer>

      <Pagination
        current={items.meta.current_page}
        pages={items.meta.last_page}
      />
    </>
  );
};

LabelsIndex.layout = (page) => <Layout title="Labels">{page}</Layout>;

export default LabelsIndex;
