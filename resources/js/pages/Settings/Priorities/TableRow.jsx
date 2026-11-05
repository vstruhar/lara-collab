import TableRowActions from '@/components/TableRowActions';
import { ColorSwatch, Table, Text } from '@mantine/core';

export default function TableRow({ item }) {
  return (
    <Table.Tr key={item.id}>
      <Table.Td w={80}>
        <ColorSwatch color={item.color} />
      </Table.Td>
      <Table.Td>
        <Text fz='sm'>{item.label}</Text>
      </Table.Td>
      <Table.Td w={100}>
        <Text fz='sm'>{item.order}</Text>
      </Table.Td>
      {(can('edit task priority') || can('delete task priority') || can('restore task priority')) && (
        <Table.Td w={100}>
          <TableRowActions
            item={item}
            editRoute='settings.task-priorities.edit'
            editPermission='edit task priority'
            archivePermission='delete task priority'
            restorePermission='restore task priority'
            archive={{
              route: 'settings.task-priorities.destroy',
              title: 'Archive priority',
              content: 'Are you sure you want to archive this priority?',
              confirmLabel: 'Archive',
            }}
            restore={{
              route: 'settings.task-priorities.restore',
              title: 'Restore priority',
              content: 'Are you sure you want to restore this priority?',
              confirmLabel: 'Restore',
            }}
          />
        </Table.Td>
      )}
    </Table.Tr>
  );
}
