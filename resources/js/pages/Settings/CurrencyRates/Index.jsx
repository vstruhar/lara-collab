import SearchInput from '@/components/SearchInput';
import TableHead from '@/components/TableHead';
import TableRowEmpty from '@/components/TableRowEmpty';
import Layout from '@/layouts/MainLayout';
import { reloadWithQuery } from '@/utils/route';
import { prepareColumns } from '@/utils/table';
import { usePage } from '@inertiajs/react';
import { Alert, Box, Grid, Group, Table, Text } from '@mantine/core';
import { IconInfoCircle } from '@tabler/icons-react';
import TableRow from './TableRow';

const CurrencyRatesIndex = () => {
  const { items, baseCurrency } = usePage().props;

  const columns = prepareColumns([
    { label: 'Currency', column: 'name', sortable: true },
    { label: 'Code', column: 'code', sortable: true },
    { label: 'Value', sortable: false },
  ]);

  const rows = items.length ? (
    items.map(item => (
      <TableRow
        baseCurrency={baseCurrency}
        item={item}
        key={item.code}
      />
    ))
  ) : (
    <TableRowEmpty colSpan={columns.length} />
  );

  const search = search => reloadWithQuery({ search });
  const sort = sort => reloadWithQuery(sort);

  return (
    <Box maw={500}>
      <Grid
        justify='space-between'
        align='center'
      >
        <Grid.Col span='content'>
          <Group>
            <SearchInput
              placeholder='Search currency rate'
              search={search}
            />
          </Group>
        </Grid.Col>
        <Grid.Col span='content'></Grid.Col>
      </Grid>

      <Alert
        variant='light'
        color='blue'
        title='Update different currency rates used by the system'
        my='lg'
        icon={<IconInfoCircle />}
      >
        <Text
          fz='md'
          mb='5px'
        >
          The base currency is set to <strong>{baseCurrency}</strong> by the owners company.
        </Text>
        <Text
          fz='xs'
          c='dimmed'
        >
          Example: if base currency is set to <strong>USD</strong>, then the currency rate for the{' '}
          <strong>EUR</strong> would be €0.9111.
        </Text>
      </Alert>

      <Table.ScrollContainer my='lg'>
        <Table verticalSpacing='sm'>
          <TableHead
            columns={columns}
            sort={sort}
          />
          <Table.Tbody>{rows}</Table.Tbody>
        </Table>
      </Table.ScrollContainer>
    </Box>
  );
};

CurrencyRatesIndex.layout = page => <Layout title='Currency rates'>{page}</Layout>;

export default CurrencyRatesIndex;
