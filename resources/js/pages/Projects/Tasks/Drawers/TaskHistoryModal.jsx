import { Label } from '@/components/Label';
import useTasksStore from '@/hooks/store/useTasksStore';
import { date, dateTime, diffForHumans } from '@/utils/datetime';
import { getInitials } from '@/utils/user';
import { usePage } from '@inertiajs/react';
import {
  Avatar,
  Box,
  Button,
  Center,
  Collapse,
  Group,
  Loader,
  Modal,
  Stack,
  Text,
  Timeline,
  Tooltip,
} from '@mantine/core';
import { useEffect, useRef, useState } from 'react';

const fieldLabels = {
  name: 'Name',
  group_id: 'Task group',
  assigned_to_user_id: 'Assignee',
  due_on: 'Due date',
  estimation: 'Time estimation',
  priority_id: 'Priority',
  pricing_type: 'Pricing type',
  fixed_price: 'Fixed price',
  billable: 'Billable',
  hidden_from_clients: 'Hidden from clients',
};

const isEmpty = (value) => value === null || value === undefined || value === '';

function HistoryValue({ field, value, currencySymbol }) {
  if (isEmpty(value)) {
    return (
      <Text span c='dimmed'>
        none
      </Text>
    );
  }

  if (field === 'due_on') {
    return <Text span>{date(value)}</Text>;
  }

  if (field === 'fixed_price') {
    return <Text span>{`${currencySymbol}${(Number(value) / 100).toFixed(2)}`}</Text>;
  }

  if (field === 'billable' || field === 'hidden_from_clients') {
    return <Text span>{value ? 'Yes' : 'No'}</Text>;
  }

  if (field === 'estimation') {
    return <Text span>{`${value} hours`}</Text>;
  }

  if (field === 'pricing_type') {
    if (value === 'fixed') {
      return <Text span>Fixed</Text>;
    }

    if (value === 'hourly') {
      return <Text span>Hourly</Text>;
    }

    return <Text span>{String(value)}</Text>;
  }

  if (field === 'priority_id') {
    return (
      <Text span fw={600} c={value.color || undefined}>
        {value.label}
      </Text>
    );
  }

  if (field === 'assigned_to_user_id' || field === 'group_id') {
    return <Text span>{value.name}</Text>;
  }

  return <Text span>{String(value)}</Text>;
}

function LabelList({ labels }) {
  return (
    <Group gap={8} wrap='wrap'>
      {labels.map((label) => (
        <Label key={label.id} name={label.name} color={label.color || 'gray'} />
      ))}
    </Group>
  );
}

function NameList({ people }) {
  return <Text size='sm'>{people.map((person) => person.name).join(', ')}</Text>;
}

function RelationChange({ entry, userName }) {
  const added = entry.new ?? [];
  const removed = entry.old ?? [];
  const isLabels = entry.field === 'labels';

  return (
    <Group gap={6} align='center' wrap='wrap'>
      <Text size='sm' fw={600}>
        {userName}
      </Text>
      {added.length > 0 && (
        <>
          <Text size='sm'>added</Text>
          {isLabels ? <LabelList labels={added} /> : <NameList people={added} />}
        </>
      )}
      {added.length > 0 && removed.length > 0 && (
        <Text size='sm'>and</Text>
      )}
      {removed.length > 0 && (
        <>
          <Text size='sm'>removed</Text>
          {isLabels ? <LabelList labels={removed} /> : <NameList people={removed} />}
        </>
      )}
    </Group>
  );
}

function DescriptionChange({ entry, isExpanded, onToggle }) {
  return (
    <Box mt={4}>
      <Button
        variant='subtle'
        size='compact-xs'
        px={0}
        onClick={onToggle}
        aria-expanded={isExpanded}
      >
        {isExpanded ? 'Hide changes' : 'Show changes'}
      </Button>
      <Collapse in={isExpanded}>
        <Stack gap='xs' mt='xs'>
          <DescriptionBody title='Before' html={entry.old} />
          <DescriptionBody title='After' html={entry.new} />
        </Stack>
      </Collapse>
    </Box>
  );
}

function DescriptionBody({ title, html }) {
  return (
    <Box>
      <Text size='xs' c='dimmed' mb={4}>
        {title}
      </Text>
      {html ? (
        <Box
          mah={200}
          p='xs'
          style={{
            overflow: 'auto',
            border: '1px solid var(--mantine-color-default-border)',
            borderRadius: 'var(--mantine-radius-sm)',
          }}
          dangerouslySetInnerHTML={{ __html: html }}
        />
      ) : (
        <Text size='sm' c='dimmed'>
          Empty
        </Text>
      )}
    </Box>
  );
}

function HistorySentence({ entry, userName, currencySymbol }) {
  if (entry.type === 'created') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        created the task
      </Text>
    );
  }

  if (entry.type === 'completed') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        completed the task
      </Text>
    );
  }

  if (entry.type === 'reopened') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        reopened the task
      </Text>
    );
  }

  if (entry.type === 'archived') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        archived the task
      </Text>
    );
  }

  if (entry.type === 'restored') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        restored the task
      </Text>
    );
  }

  if (entry.field === 'description') {
    return (
      <Text size='sm'>
        <Text span fw={600}>
          {userName}
        </Text>{' '}
        changed the description
      </Text>
    );
  }

  if (entry.field === 'labels' || entry.field === 'subscribed_users') {
    return <RelationChange entry={entry} userName={userName} />;
  }

  const fieldLabel = fieldLabels[entry.field] ?? 'a field';

  return (
    <Text size='sm'>
      <Text span fw={600}>
        {userName}
      </Text>{' '}
      changed {fieldLabel} from{' '}
      <HistoryValue field={entry.field} value={entry.old} currencySymbol={currencySymbol} /> to{' '}
      <HistoryValue field={entry.field} value={entry.new} currencySymbol={currencySymbol} />
    </Text>
  );
}

function HistoryEntry({ entry, currencySymbol, isExpanded, onToggleDescription }) {
  const userName = entry.user?.name || 'Someone';

  const handleToggleDescription = () => {
    onToggleDescription(entry.id);
  };

  return (
    <Timeline.Item
      bullet={
        <Avatar src={entry.user?.avatar || null} radius='xl' color='blue' size={32} alt=''>
          {entry.user?.name ? getInitials(entry.user.name) : '?'}
        </Avatar>
      }
    >
      <HistorySentence entry={entry} userName={userName} currencySymbol={currencySymbol} />
      {entry.field === 'description' && (
        <DescriptionChange
          entry={entry}
          isExpanded={isExpanded}
          onToggle={handleToggleDescription}
        />
      )}
      <Tooltip label={dateTime(entry.created_at)} openDelay={250} withArrow>
        <Text size='xs' c='dimmed' mt={4} w='fit-content'>
          {diffForHumans(entry.created_at)}
        </Text>
      </Tooltip>
    </Timeline.Item>
  );
}

export default function TaskHistoryModal({ task, opened, onClose }) {
  const { history, historyHasMore, fetchHistory } = useTasksStore();
  const { currency } = usePage().props;
  const currencySymbol = currency?.symbol || '';
  const [isLoading, setIsLoading] = useState(false);
  const [isLoadingMore, setIsLoadingMore] = useState(false);
  const [page, setPage] = useState(1);
  const [expandedId, setExpandedId] = useState(null);
  const loadedTaskId = useRef(null);

  const showLoading = isLoading || (opened && task && loadedTaskId.current !== task.id);

  useEffect(() => {
    if (!opened) {
      loadedTaskId.current = null;
    }
  }, [opened]);

  useEffect(() => {
    if (!opened || !task) {
      return undefined;
    }

    let isCurrent = true;
    setIsLoading(true);
    setIsLoadingMore(false);
    setPage(1);
    setExpandedId(null);

    fetchHistory(task, 1, () => {
      if (!isCurrent) {
        return;
      }

      loadedTaskId.current = task.id;
      setIsLoading(false);
    });

    return () => {
      isCurrent = false;
    };
  }, [opened, task?.id, fetchHistory]);

  const handleToggleDescription = (entryId) => {
    setExpandedId((current) => (current === entryId ? null : entryId));
  };

  const handleLoadMore = () => {
    if (!task || isLoadingMore) {
      return;
    }

    const nextPage = page + 1;
    setIsLoadingMore(true);
    fetchHistory(task, nextPage, (error) => {
      setIsLoadingMore(false);

      if (!error) {
        setPage(nextPage);
      }
    });
  };

  return (
    <Modal opened={opened} onClose={onClose} title='Task history' size='lg' zIndex={400}>
      {showLoading ? (
        <Center mih={160}>
          <Loader color='blue' />
        </Center>
      ) : (
        <>
          {history.length === 0 ? (
            <Center mih={160}>
              <Text c='dimmed' size='sm'>
                No history yet
              </Text>
            </Center>
          ) : (
            <Timeline active={history.length} bulletSize={32} lineWidth={2}>
              {history.map((entry) => (
                <HistoryEntry
                  key={entry.id}
                  entry={entry}
                  currencySymbol={currencySymbol}
                  isExpanded={expandedId === entry.id}
                  onToggleDescription={handleToggleDescription}
                />
              ))}
            </Timeline>
          )}
          {historyHasMore && (
            <Center mt='md'>
              <Button variant='subtle' onClick={handleLoadMore} loading={isLoadingMore}>
                Load more
              </Button>
            </Center>
          )}
        </>
      )}
    </Modal>
  );
}
