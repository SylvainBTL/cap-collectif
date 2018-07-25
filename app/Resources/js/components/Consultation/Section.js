// @flow
import * as React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import OpinionList from './OpinionList';
import type { Section_section } from './__generated__/Section_section.graphql';

type Props = {
  section: Section_section,
  consultation: Object,
  level: number,
};

export class Section extends React.Component<Props> {
  render() {
    const { consultation, section, level } = this.props;
    return (
      <div
        id={`opinion-type--${section.slug}`}
        className={`anchor-offset text-center level--${level}`}>
        {section.title}
        <br />
        {section.subtitle && <span className="small excerpt">{section.subtitle}</span>}
        {(section.contributionsCount > 0 || section.contribuable) && (
          <div style={{ marginTop: 15 }}>
            <OpinionList consultation={consultation} section={section} />
          </div>
        )}
      </div>
    );
  }
}

export default createFragmentContainer(
  Section,
  graphql`
    fragment Section_section on Section {
      title
      slug
      subtitle
      contribuable
      contributionsCount
      ...OpinionList_section
    }
  `,
);
