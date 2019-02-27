import React from "react";
import ReactDOM from "react-dom";
import { Job, Application, ReviewStatus } from "./types";
import className from "classnames";
import route from "../helpers/route";
import Select, { SelectOption } from "./Select";

type Bucket = "priority" | "citizen" | "non-citizen" | "unqualified";

type Category = "primary" | "optional" | "screened-out";

/* Functions used by multiple sub-components */
/**
 * Returns true if application has been screened out.
 */
function isScreenedOut(application: Application): boolean {
  return false; // TODO: decide how to determin
}

/**
 * Return the bucket this application belongs to. Either:
 *  priority
 *  citizen
 *  secondary
 *  unqualified
 *
 */
function applicationBucket(application: Application): Bucket {
  if (false) {
    return "priority"; // TODO: decide how to determine priority
  } else if (application.citizenship_declaration.name === "citizen") {
    return "citizen";
  } else {
    return "non-citizen";
  }
  return "unqualified";
  // TODO: decide how to determine unqualified
}

/**
 * Return the category this application belongs to. Either:
 *  primary
 *  optional
 *  screened-out
 * @param {Application} application
 */
function applicationCategory(application: Application): Category {
  if (isScreenedOut(application)) {
    return "screened-out";
  }
  const bucket = applicationBucket(application);
  switch (bucket) {
    case "priority":
    case "citizen":
      return "primary";
    case "non-citizen":
    case "unqualified":
    default:
      return "optional";
  }
}

interface ApplicationViewProps {
  application: Application;
  reviewStatusOptions: SelectOption<number>[];
}

/**
 * Applicants and Their States
 * Applicants contain 3 different points of data that can alter their state:
 *   - Their current status:
 *     - in (screened in)
 *     - out (screened out)
 *     - maybe (saved for review)
 *     - null (the manager hasn't yet taken an action on this applicant)
 *   - Whether a note has been created regarding their application:
 *     - true
 *     - false
 */
const ApplicationView: React.FunctionComponent<ApplicationViewProps> = (
  props
): React.ReactElement => {
  const reviewStatus =
    props.application.application_review &&
    props.application.application_review.review_status
      ? props.application.application_review.review_status.name
      : null;
  const statusIconClass = className("fas", {
    "fa-ban": reviewStatus == "screened_out",
    "fa-question-circle": reviewStatus == "still_thinking",
    "fa-check-circle": reviewStatus == "still_in",
    "fa-exclamation-circle": reviewStatus == null
  });

  return (
    <form className="applicant-summary">
      <div className="flex-grid middle gutter">
        <div className="box lg-1of11 applicant-status">
          <i className={statusIconClass} />
        </div>

        <div className="box lg-2of11 applicant-information">
          <span className="name">{props.application.applicant.user.name}</span>
          <a
            href={"mailto:" + props.application.applicant.user.email}
            title="Email this candidate."
          >
            {props.application.applicant.user.email}
          </a>
          {/* This span only shown for veterans */}
          {(props.application.veteran_status.name == "current" ||
            props.application.veteran_status.name == "past") && (
            <span className="veteran-status">
              <img
                alt="The Talent Cloud veteran icon."
                src="/images/icon_veteran.svg"
              />
              Veteran
            </span>
          )}
        </div>

        <div className="box lg-2of11 applicant-links">
          <a
            href={route("manager.applications.show", props.application)}
            title="View this applicant's application."
          >
            <i className="fas fa-file-alt" />
            View Application
          </a>
          <a
            href={route("manager.applicants.show", props.application.applicant)}
            title="View this applicant's profile."
          >
            <i className="fas fa-user" />
            View Profile
          </a>
        </div>

        <div className="box lg-2of11 applicant-decision">
          <Select
            formName="review_status"
            htmlId={"review_status_" + props.application.id}
            label="Decision"
            selected={
              props.application.application_review &&
              props.application.application_review.review_status_id
                ? props.application.application_review.review_status_id
                : undefined
            }
            nullSelection="Not Reviewed"
            options={props.reviewStatusOptions}
          />
        </div>

        <div className="box lg-2of11 applicant-notes">
          {/* Add a Note
                        This button should trigger a dialogue that allows the manager to edit and save a textarea element unique to this applicant/application. This dialoue should contain a "Cancel" and "Save" button.
                        Change the text depending on whether note exists yet
                    */}
          <button className="button--outline" type="button">
            + Add a Note / Edit Note
          </button>
        </div>

        <div className="box lg-2of11 applicant-save-button">
          {/* Save Button
                        This button should be given a "saved" class when React is finished submitting the data. This class should then be removed when any element for this applicant has been changed, prompting the user to save again.
                    */}
          <button className="button--blue light-bg" type="button">
            <span className="default-copy">Save</span>
            <span className="saved-copy">Saved</span>
          </button>
        </div>
      </div>
    </form>
  );
};

interface BucketViewProps {
  title: string;
  description: string;
  applications: Application[];
  reviewStatusOptions: SelectOption<number>[];
}

/**
 * Applicant Buckets
 * There are 4 applicant buckets:
 *  - [1] Priority Applicants (this bucket will not be used at first and is replaced by the "temporary priority alert outlined above.)
 *  - [2] Veteran & Citzen Applicants
 *  - [3] Non-Canadian Applicants
 *  - [4] Unqualified Applicants (These applicants claimed to have the required essential criteria at a lower level than the job poster asked for.)
 * The larger page categories outlined earlier contain unique combinations of these buckets:
 *  - 1 and 2 appear in the "primary" category
 *  - 3 and 4 appear in the "secondary" category
 *  - The "tertiary" category contains all 4, each displaying only the candidates that have been screened out in that bucket.
 */
const BucketView: React.StatelessComponent<BucketViewProps> = (
  props
): React.ReactElement => {
  return (
    <div className="accordion applicant-bucket">
      <button
        aria-expanded="false"
        className="accordion-trigger"
        tabIndex={0}
        type="button"
      >
        <span className="bucket-title">
          <i className="fas fa-ban" /> {props.title} (
          {props.applications.length})
        </span>

        <span className="invisible">
          Toggle this step to view relevant applicants.
        </span>

        <i className="fas fa-chevron-up" />
      </button>

      {/* Accordion Content */}
      <div aria-hidden="true" className="accordion-content">
        <p>{props.description}</p>

        {props.applications.map(application => (
          <ApplicationView
            key={application.id}
            application={application}
            reviewStatusOptions={props.reviewStatusOptions}
          />
        ))}
      </div>
    </div>
  );
};

interface CategoryViewProps {
  title: string;
  description: string;
  showScreenOutAll: boolean;
  applications: Application[];
  reviewStatusOptions: SelectOption<number>[];
}

const CategoryView: React.StatelessComponent<CategoryViewProps> = (
  props
): React.ReactElement => {
  {
    /* Applicant Categories
            Categories have 3 class determined states:
                - primary (priority, veteran, and citizen candidates)
                - secondary (non-canadians, unqualified canadians)
                - tertiary (all candidates who have been screened out)
        */
  }

  const buckets = [
    {
      title: "Priority Applicants",
      description: "blah",
      applications: props.applications.filter(
        application => applicationBucket(application) === "priority"
      )
    },
    {
      title: "Canadian Citizens and Veterans",
      description: "blah",
      applications: props.applications.filter(
        application => applicationBucket(application) === "citizen"
      )
    },
    {
      title: "Non-Canadian Citizens",
      description: "blah",
      applications: props.applications.filter(
        application => applicationBucket(application) === "non-citizen"
      )
    },
    {
      title: "Don't Meed Essential Criteria",
      description: "blah",
      applications: props.applications.filter(
        application => applicationBucket(application) === "unqualified"
      )
    }
  ];

  return (
    <div className="applicant-category">
      <h3 className="heading--03">
        <i className="fas fa-ban" /> {props.title}
      </h3>

      <p>{props.description}</p>

      {/* Category Action
                This section only exists for the "secondary" category, and should generate a confirmation dialogue that prompts the user to decide whether to screen ALL of the candidates in this category out or not.
            */}
      {props.showScreenOutAll && (
        <span className="category-action">
          <button className="button--outline" type="button">
            <i className="fas fa-ban" /> Screen All Optional Candidates Out
          </button>
        </span>
      )}

      {buckets.map(bucket => (
        <BucketView
          key={bucket.title}
          {...bucket}
          reviewStatusOptions={props.reviewStatusOptions}
        />
      ))}
    </div>
  );
};

interface ReviewApplicationsViewProps {
  title: string;
  classification: string;
  applications: Application[];
  reviewStatusOptions: SelectOption<number>[];
}

const ReviewApplicationsView: React.StatelessComponent<
  ReviewApplicationsViewProps
> = (props): React.ReactElement => {
  const categories = [
    {
      title: "Under Consideration",
      description: "Blah blah",
      showScreenOutAll: false,
      applications: props.applications.filter(
        application => applicationCategory(application) == "primary"
      )
    },
    {
      title: "Optional Consideration",
      description: "Blah blah",
      showScreenOutAll: true,
      applications: props.applications.filter(
        application => applicationCategory(application) == "optional"
      )
    },
    {
      title: "No Longer Under Consideration",
      description: "Blah blah",
      showScreenOutAll: false,
      applications: props.applications.filter(
        application => applicationCategory(application) == "screened-out"
      )
    }
  ];

  return (
    <section className="applicant-review container--layout-xl">
      <div className="flex-grid gutter">
        <div className="box med-1of2 job-title-wrapper">
          <span>
            Viewing Applicants for: {props.title} ({props.classification})
          </span>
        </div>

        <div className="box med-1of2 timer-wrapper">
          <span>
            <i className="fas fa-stopwatch" /> {/* Number */} Days Since Close
          </span>
        </div>
      </div>

      <div className="priority-alert">
        <h3>
          <i className="fas fa-bell" /> Temporary Priority Alert
        </h3>

        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
          luctus fermentum lorem, vel rhoncus velit vehicula imperdiet. Integer
          ullamcorper iaculis justo, quis tincidunt ex vulputate ut. Vivamus
          molestie augue turpis, ut egestas ante aliquam id. Quisque efficitur,
          metus imperdiet rhoncus pharetra, velit ligula lobortis tortor, vitae
          imperdiet leo augue ac velit. Vivamus sollicitudin dictum est a
          tempus. Fusce tempus finibus elit sed lacinia.
        </p>
      </div>
      {categories.map(category => (
        <CategoryView
          key={category.title}
          {...category}
          reviewStatusOptions={props.reviewStatusOptions}
        />
      ))}
    </section>
  );
};

interface ReviewApplicationsProps {
  job: Job;
  initApplications: Application[];
  reviewStatuses: ReviewStatus[];
}

interface ReviewApplicationsState {
  applications: Application[];
}

export default class ReviewApplications extends React.Component<
  ReviewApplicationsProps,
  ReviewApplicationsState
> {
  public constructor(props: ReviewApplicationsProps) {
    super(props);
    this.state = {
      applications: props.initApplications
    };
  }

  public render(): React.ReactElement {
    const reviewStatusOptions = this.props.reviewStatuses.map(status => {
      return { value: status.id, label: status.name };
    });

    return (
      <ReviewApplicationsView
        title={this.props.job.title}
        classification={this.props.job.classification}
        applications={this.state.applications}
        reviewStatusOptions={reviewStatusOptions}
      />
    );
  }
}

if (document.getElementById("review-applications")) {
  const container = document.getElementById(
    "review-applications"
  ) as HTMLElement;
  if (
    container.hasAttribute("data-job") &&
    container.hasAttribute("data-applications") &&
    container.hasAttribute("data-review-statuses")
  ) {
    const job = JSON.parse(container.getAttribute("data-job") as string);
    const applications = JSON.parse(container.getAttribute(
      "data-applications"
    ) as string);
    const reviewStatuses = JSON.parse(container.getAttribute(
      "data-review-statuses"
    ) as string);
    ReactDOM.render(
      <ReviewApplications
        job={job}
        initApplications={applications}
        reviewStatuses={reviewStatuses}
      />,
      container
    );
  }
}
