import { FC, useEffect, useState } from 'react';
import { useIntl } from 'react-intl';
import { graphql, useFragment } from 'react-relay';
import { DateRange, Flex, Select } from '@cap-collectif/ui';
import type {
    DashboardFilters_viewer,
    DashboardFilters_viewer$key,
} from '@relay/DashboardFilters_viewer.graphql';
import { DEFAULT_FILTERS, FilterKey, useDashboard } from '../Dashboard.context';
import { useAppContext } from '../../AppProvider/App.context';
import moment from 'moment';

const FRAGMENT = graphql`
    fragment DashboardFilters_viewer on User
    @argumentDefinitions(affiliations: { type: "[ProjectAffiliation!]" }) {
        projects(affiliations: $affiliations) {
            totalCount
            edges {
                node {
                    id
                    title
                    timeRange {
                        startAt
                        endAt
                    }
                }
            }
        }
    }
`;

const getDateRangeProject = (
    projects: DashboardFilters_viewer['projects'],
    projectIdSelected: string,
) => {
    const projectSelected = projects?.edges
        ?.filter(Boolean)
        .map(edge => edge?.node)
        .filter(Boolean)
        .find(project => project && project.id === projectIdSelected);

    if (projectSelected && projectSelected.timeRange) {
        return {
            startAt: projectSelected.timeRange?.startAt || DEFAULT_FILTERS.dateRange.startAt,
            endAt: projectSelected.timeRange?.endAt || DEFAULT_FILTERS.dateRange.endAt,
        };
    }

    return {
        startAt: DEFAULT_FILTERS.dateRange.startAt,
        endAt: DEFAULT_FILTERS.dateRange.endAt,
    };
};

type DashboardFiltersProps = {
    viewer: DashboardFilters_viewer$key,
};

const DashboardFilters: FC<DashboardFiltersProps> = ({ viewer: viewerFragment }) => {
    const viewer = useFragment(FRAGMENT, viewerFragment);
    const intl = useIntl();
    const { viewerSession } = useAppContext();
    const { setFilters, filters } = useDashboard();
    const { projects } = viewer;
    const [dateRange, setDateRange] = useState({
        startDate: moment(filters.dateRange.startAt),
        endDate: moment(filters.dateRange.endAt),
    });
    const canSelectDateOutRange = filters[FilterKey.PROJECT] === 'ALL';

    useEffect(() => {
        setDateRange({
            startDate: moment(filters.dateRange.startAt),
            endDate: moment(filters.dateRange.endAt),
        });
    }, [filters.dateRange]);

    useEffect(() => {
        if (filters.projectId !== 'ALL') {
            const dateRangeProject = getDateRangeProject(projects, filters.projectId);
            setFilters(FilterKey.DATE_RANGE, JSON.stringify(dateRangeProject));
        }
    }, [filters[FilterKey.PROJECT], projects]);

    const defaultValue =
        projects?.totalCount > 0
            ? projects?.edges
                  ?.filter(Boolean)
                  .map(edge => edge?.node)
                  .filter(Boolean)
                  .find(project => project && project.id === filters.projectId) || 'ALL'
            : 'ALL';

    const defaultValueFormatted =
        defaultValue === 'ALL'
            ? {
                  label: intl.formatMessage({ id: 'global.all.projects' }),
                  value: 'ALL',
              }
            : {
                  id: defaultValue.id,
                  label: defaultValue.title,
              };

    return (
        <Flex direction="row" align="center" spacing={2}>
            <Select
                onChange={optionSelected => setFilters(FilterKey.PROJECT, optionSelected.value)}
                defaultValue={defaultValueFormatted}
                width="20%"
                options={[
                    ...(viewerSession.isAdmin
                        ? [
                              {
                                  label: intl.formatMessage({ id: 'global.all.projects' }),
                                  value: 'ALL',
                              },
                          ]
                        : []),
                    ...(projects?.totalCount > 0
                        ? projects?.edges
                              ?.filter(Boolean)
                              .map(edge => edge?.node)
                              .filter(Boolean)
                              .map(
                                  project =>
                                      project && {
                                          label: project.title,
                                          value: project.id,
                                      },
                              ) || []
                        : []),
                ]}
            />

            <DateRange
                isOutsideRange={() => false}
                value={dateRange}
                onChange={({ startDate, endDate }) => {
                    setDateRange({
                        startDate: startDate || dateRange.startDate,
                        endDate: endDate || dateRange.endDate,
                    });
                }}
                minDate={canSelectDateOutRange ? undefined : dateRange.startDate}
                displayFormat="DD/MM/YYYY"
                onClose={({ startDate, endDate }) => {
                    const dateRangeUpdated = {
                        startAt: startDate
                            ? startDate.format('MM/DD/YYYY')
                            : dateRange.startDate.format('MM/DD/YYYY'),
                        endAt: endDate
                            ? endDate.format('MM/DD/YYYY')
                            : dateRange.endDate.format('MM/DD/YYYY'),
                    };

                    setFilters(FilterKey.DATE_RANGE, JSON.stringify(dateRangeUpdated));
                }}
            />
        </Flex>
    );
};

export default DashboardFilters;
