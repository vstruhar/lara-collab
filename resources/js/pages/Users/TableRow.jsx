import React from "react";
import { rem } from "@mantine/core";
import { money } from "@/utils/formatCurrency";
import { IconPencil, IconDots, IconTrash } from "@tabler/icons-react";
import {
  Avatar,
  Badge,
  Table,
  Group,
  Text,
  ActionIcon,
  Menu,
} from "@mantine/core";
import UserService from "@/services/UserService";
import RoleBadge from "@/components/RoleBadge";

export default function TableRow({ user }) {
  const service = new UserService(user);

  return (
    <Table.Tr key={user.id}>
      <Table.Td>
        <Group gap="sm">
          <Avatar
            src={user.avatar}
            size={40}
            radius={40}
            color="blue"
            alt={user.name}
          >
            {service.getInitials()}
          </Avatar>
          <div>
            <Text fz="sm" fw={500}>
              {user.name}
            </Text>
            <Text fz="xs" c="dimmed">
              {user.job_title}
            </Text>
          </div>
        </Group>
      </Table.Td>
      <Table.Td>
        {user.roles.map((role) => (
          <RoleBadge role={role.name} key={role.id} />
        ))}
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{user.email}</Text>
        <Text fz="xs" c="dimmed">
          Email
        </Text>
      </Table.Td>
      <Table.Td>
        <Text fz="sm">{money(user.rate)} / hr</Text>
        <Text fz="xs" c="dimmed">
          Rate
        </Text>
      </Table.Td>
      <Table.Td>
        <Group gap={0} justify="flex-end">
          <ActionIcon variant="subtle" color="blue">
            <IconPencil
              style={{
                width: rem(16),
                height: rem(16),
              }}
              stroke={1.5}
            />
          </ActionIcon>
          <Menu
            transitionProps={{
              transition: "pop",
            }}
            withArrow
            position="bottom-end"
            withinPortal
          >
            <Menu.Target>
              <ActionIcon variant="subtle" color="gray">
                <IconDots
                  style={{
                    width: rem(16),
                    height: rem(16),
                  }}
                  stroke={1.5}
                />
              </ActionIcon>
            </Menu.Target>
            <Menu.Dropdown>
              <Menu.Item
                leftSection={
                  <IconTrash
                    style={{
                      width: rem(16),
                      height: rem(16),
                    }}
                    stroke={1.5}
                  />
                }
                color="red"
              >
                Delete
              </Menu.Item>
            </Menu.Dropdown>
          </Menu>
        </Group>
      </Table.Td>
    </Table.Tr>
  );
}
