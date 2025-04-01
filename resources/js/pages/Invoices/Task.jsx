import { Label } from '@/components/Label';
import { money } from '@/utils/currency';
import { date } from '@/utils/datetime';
import { openInNewTab } from '@/utils/route';
import {
  Checkbox,
  Flex,
  Group,
  Pill,
  Stack,
  Text,
  Tooltip,
  rem,
  useComputedColorScheme,
} from '@mantine/core';
import classes from './css/Task.module.css';
import { PricingType } from '@/utils/enums';

export default function Task({ task, selectedTasks, toggleTask, currency, type, hourlyRate }) {
  const computedColorScheme = useComputedColorScheme();

  return (
    <Flex
      className={classes.task}
      justify='space-between'
      wrap='nowrap'
    >
      <Group
        gap='sm'
        wrap='nowrap'
        align='self-start'
      >
        <Checkbox
          size='sm'
          className={classes.checkbox}
          checked={selectedTasks.includes(task.id)}
          onChange={event => toggleTask(task.id, event.currentTarget.checked)}
        />
        <Stack gap={3}>
          <Text
            className={classes.name}
            size='sm'
            fw={500}
            onClick={() => openInNewTab('projects.tasks.open', [task.project_id, task.id])}
          >
            #{task.number + ': ' + task.name}
          </Text>

          <Group
            wrap='wrap'
            style={{
              rowGap: rem(3),
              columnGap: rem(12),
            }}
          >
            {task.labels.map(label => (
              <Label
                key={label.id}
                name={label.name}
                color={label.color}
              />
            ))}
          </Group>
        </Stack>
      </Group>
      <Pill
        size='sm'
        fw={600}
        variant='default'
        c={
          task.pricing_type === PricingType.HOURLY
            ? computedColorScheme === 'light'
              ? 'orange.8'
              : 'orange.2'
            : computedColorScheme === 'light'
              ? 'green.8'
              : 'green.2'
        }
        bg={
          task.pricing_type === PricingType.HOURLY
            ? computedColorScheme === 'light'
              ? 'orange.1'
              : 'orange.9'
            : computedColorScheme === 'light'
              ? 'green.1'
              : 'green.9'
        }
      >
        {task.pricing_type === PricingType.HOURLY ? 'Hourly' : 'Fixed price'}
      </Pill>
      <Stack
        gap={3}
        ml={10}
        style={{
          flexShrink: 0,
        }}
      >
        {type === 'default' &&
          (task.pricing_type === PricingType.HOURLY ? (
            <Tooltip
              label={
                Number(task.total_minutes) === 0
                  ? 'There is no logged time on this task'
                  : `Logged time: ${Number(task.total_minutes) / 60}h`
              }
              openDelay={500}
              withArrow
            >
              <Text
                fw={700}
                c={Number(task.total_minutes) === 0 ? 'red.7' : ''}
              >
                {money((Number(task.total_minutes) / 60) * hourlyRate, currency.code)}
              </Text>
            </Tooltip>
          ) : (
            <Text
              fw={700}
              c={Number(task.fixed_price) === 0 ? 'red.7' : ''}
            >
              {money(task.price, currency.code)}
            </Text>
          ))}
        <Text
          size='xs'
          c='dimmed'
          fw={500}
        >
          {date(task.completed_at)}
        </Text>
      </Stack>
    </Flex>
  );
}
