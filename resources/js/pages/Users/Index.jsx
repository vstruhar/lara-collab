import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import Pagination from "@/components/Pagination";
import SearchInput from "@/components/SearchInput";
import TableHead from "@/components/TableHead";
import Layout from "@/layouts/MainLayout";
import { redirectTo, reloadWithQuery } from "@/utils/route";
import { usePage } from "@inertiajs/react";
import { Button, Grid, Group, Table } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";
import remove from "lodash/remove";
import TableRow from "./TableRow";

const UsersIndex = () => {
  const { items } = usePage().props;

  const columns = [
    { label: "User", column: "name" },
    { label: "Role", sortable: false },
    { label: "Email", column: "email" },
    { label: "Rate", column: "rate" },
    { label: "Actions", sortable: false },
  ];

  if (!can("view user rate")) remove(columns, (i) => i.label === "Rate");
  if (!can("edit user") && !can("archive user"))
    remove(columns, (i) => i.label === "Actions");

  const rows = items.data.map((user) => <TableRow item={user} key={user.id} />);
  const search = (search) => reloadWithQuery({ search });
  const sort = (sort) => reloadWithQuery(sort);

  return (
    <>
      <Grid justify="space-between" align="center">
        <Grid.Col span="content">
          <Group>
            <SearchInput placeholder="Search users" search={search} />
            <ArchivedFilterButton />
          </Group>
        </Grid.Col>
        <Grid.Col span="content">
          {can("create user") && (
            <Button
              leftSection={<IconPlus size={14} />}
              radius="xl"
              onClick={redirectTo("users.create")}
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

      <Pagination
        current={items.meta.current_page}
        pages={items.meta.last_page}
      />
    </>
  );
};

UsersIndex.layout = (page) => <Layout children={page} title="Users" />;

export default UsersIndex;
