import RoleBadge from "@/components/RoleBadge";
import TableRowActions from "@/components/TableRowActions";
import { money } from "@/utils/currency";
import { getInitials } from "@/utils/user";
import { Avatar, Flex, Group, Table, Text } from "@mantine/core";

export default function TableRow({ item }) {
  return (
    <Table.Tr key={item.id}>
      <Table.Td>
        <Group gap="sm">
          <Avatar
            src={item.avatar}
            size={40}
            radius={40}
            color="blue"
            alt={item.name}
          >
            {getInitials(item.name)}
          </Avatar>
          <div>
            <Text fz="sm" fw={500}>
              {item.name}
            </Text>
            <Text fz="xs" c="dimmed">
              {item.job_title}
            </Text>
          </div>
        </Group>
      </Table.Td>
      <Table.Td maw={200}>
        <Flex gap="sm" align="center" direction="row" wrap="wrap">
          {item.roles.map((role, index) => (
            <RoleBadge role={role} key={`role-${index}-${item.id}`} />
          ))}
        </Flex>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{item.email}</Text>
        <Text fz="xs" c="dimmed">
          Email
        </Text>
      </Table.Td>
      {can("view user rate") && (
        <Table.Td>
          <Text fz="sm">{money(item.rate)} / hr</Text>
          <Text fz="xs" c="dimmed">
            Rate
          </Text>
        </Table.Td>
      )}
      {(can("edit user") || can("archive user") || can("restore user")) && (
        <Table.Td>
          <TableRowActions
            item={item}
            editRoute="users.edit"
            editPermission="edit user"
            archivePermission="archive user"
            restorePermission="restore user"
            archive={{
              route: "users.destroy",
              title: "Archive user",
              content: `Are you sure you want to archive this user? This action will prevent
                the user from logging in, while all other aspects related to the
                user's actions will remain unaffected.`,
              confirmLabel: "Archive",
            }}
            restore={{
              route: "users.restore",
              title: "Restore user",
              content: `Are you sure you want to restore this user? This action will allow the user to login.`,
              confirmLabel: "Restore",
            }}
          />
        </Table.Td>
      )}
    </Table.Tr>
  );
}
