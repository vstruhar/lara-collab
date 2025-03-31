import { NumberInput, Table, Text } from '@mantine/core';

export default function TableRow({ item, baseCurrency }) {
  return (
    <Table.Tr key={item}>
      <Table.Td>
        <Text fz='sm'>{item.name}</Text>
      </Table.Td>
      <Table.Td>
        <Text fz='sm'>{item.code}</Text>
      </Table.Td>
      <Table.Td>
        <NumberInput
          placeholder='1.0000'
          prefix={item.symbol}
          defaultValue={1.0}
          mb='md'
          required
          allowNegative={false}
          allowDecimal={true}
          decimalScale={4}
          fixedDecimalScale
          hideControls
        />
      </Table.Td>
    </Table.Tr>
  );
}
