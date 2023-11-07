import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import SearchInput from "@/components/SearchInput";
import { reloadWithQuery } from "@/utils/route";
import { Button, Grid, Group } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";

export default function Header() {
  const search = (search) => reloadWithQuery({ search });

  return (
    <Grid justify="space-between" align="center">
      <Grid.Col span="content">
        <Group>
          <SearchInput placeholder="Search tasks" search={search} />
          <ArchivedFilterButton />
        </Group>
      </Grid.Col>
      <Grid.Col span="content">
        {can("create task") && (
          <Button leftSection={<IconPlus size={14} />} radius="xl">
            Create
          </Button>
        )}
      </Grid.Col>
    </Grid>
  );
}
