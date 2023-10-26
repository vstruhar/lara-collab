import Layout from "@/layouts/MainLayout";
import { usePage } from "@inertiajs/react";
import { Table, Button, Grid } from "@mantine/core";
import Pagination from "@/components/Pagination";
import TableRow from "./TableRow";
import { IconPlus } from "@tabler/icons-react";
import { SearchButton } from "@/components/SearchButton";
import { redirectWithQuery } from "@/utils/route";
import TableHead from "@/components/TableHead";

const UsersIndex = () => {
  const { users } = usePage().props;

  const columns = [
    { label: "User", column: "name" },
    { label: "Role", sortable: false },
    { label: "Email", column: "email" },
    { label: "Rate", column: "rate" },
    { label: "Actions", sortable: false },
  ];

  const rows = users.data.map((user) => <TableRow user={user} key={user.id} />);

  const searchUsers = (search) => redirectWithQuery({ search });
  const sortUsers = (sort) => redirectWithQuery(sort);

  return (
    <>
      <Grid justify="space-between" align="center">
        <Grid.Col span="content">
          <SearchButton placeholder="Search users" search={searchUsers} />
        </Grid.Col>
        <Grid.Col span="content">
          <Button leftSection={<IconPlus size={14} />} radius="xl">
            Create
          </Button>
        </Grid.Col>
      </Grid>

      <Table.ScrollContainer minWidth={800} my="lg">
        <Table verticalSpacing="sm">
          <TableHead columns={columns} sort={sortUsers} />
          <Table.Tbody>{rows}</Table.Tbody>
        </Table>
      </Table.ScrollContainer>

      <Pagination current={users.current_page} pages={users.last_page} />
    </>
  );
};

UsersIndex.layout = (page) => <Layout children={page} title="Users" />;

export default UsersIndex;
