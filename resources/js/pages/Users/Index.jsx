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
  const { users } = usePage().props;

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

  const rows = users.data.map((user) => <TableRow user={user} key={user.id} />);
  const searchUsers = (search) => reloadWithQuery({ search });
  const sortUsers = (sort) => reloadWithQuery(sort);

  return (
    <>
      <Grid justify="space-between" align="center">
        <Grid.Col span="content">
          <Group>
            <SearchInput placeholder="Search users" search={searchUsers} />
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

      <Table.ScrollContainer minWidth={800} my="lg">
        <Table verticalSpacing="sm">
          <TableHead columns={columns} sort={sortUsers} />
          <Table.Tbody>{rows}</Table.Tbody>
        </Table>
      </Table.ScrollContainer>

      <Pagination
        current={users.meta.current_page}
        pages={users.meta.last_page}
      />
    </>
  );
};

UsersIndex.layout = (page) => <Layout children={page} title="Users" />;

export default UsersIndex;
