import { Table, UnstyledButton, Group, Text, Center, rem } from "@mantine/core";
import {
  IconSelector,
  IconChevronDown,
  IconChevronUp,
} from "@tabler/icons-react";
import classes from "./css/TableHeader.module.css";

export default function TableHeader({
  children,
  reversed,
  sorted,
  sortable,
  onSort,
}) {
  const Icon = sorted
    ? reversed
      ? IconChevronUp
      : IconChevronDown
    : IconSelector;
  return (
    <Table.Th className={classes.th}>
      {sortable !== false ? (
        <UnstyledButton onClick={onSort} className={classes.control}>
          <Group justify="space-between">
            <Text fw={500} fz="sm">
              {children}
            </Text>
            <Center className={classes.icon}>
              <Icon style={{ width: rem(16), height: rem(16) }} stroke={1.5} />
            </Center>
          </Group>
        </UnstyledButton>
      ) : (
        <UnstyledButton
          className={classes.control}
          style={{ cursor: "default" }}
        >
          <Text fw={500} fz="sm">
            {children}
          </Text>
        </UnstyledButton>
      )}
    </Table.Th>
  );
}
