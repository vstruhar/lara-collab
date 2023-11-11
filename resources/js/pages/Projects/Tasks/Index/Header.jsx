import ArchivedFilterButton from "@/components/ArchivedFilterButton";
import ClearFiltersButton from "@/components/ClearFiltersButton";
import SearchInput from "@/components/SearchInput";
import useTaskFiltersStore from "@/hooks/store/useTaskFiltersStore";
import { reloadWithQuery } from "@/utils/route";
import { Button, Grid, Group } from "@mantine/core";
import { IconPlus } from "@tabler/icons-react";

export default function Header() {
  const search = (search) => reloadWithQuery({ search });

  const { hasUrlParams } = useTaskFiltersStore();
  const usingFilters = hasUrlParams(["archived"]);

  return (
    <Grid justify="space-between" align="center">
      <Grid.Col span="content">
        <Group>
          <SearchInput placeholder="Search tasks" search={search} mr="md" />
          <ArchivedFilterButton />
          {usingFilters && <ClearFiltersButton />}
        </Group>
      </Grid.Col>
      <Grid.Col span="content">
        {can("create task") && (
          <Button leftSection={<IconPlus size={14} />} radius="xl">
            Add task
          </Button>
        )}
      </Grid.Col>
    </Grid>
  );
}
